<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\WfqScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WfqSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['order_nodes.device_token' => 'test-device-token']);
    }

    public function test_existing_menu_finish_times_are_not_recalculated(): void
    {
        $user = $this->createTestUser();
        $firstMilkOrder = $this->createTestOrder($user, 'Kopi Susu Saja', 2, 3, 0, 3);
        $firstMilkFinish = $firstMilkOrder->wfq_finish_time;

        $blackOrder = $this->createTestOrder($user, 'Kopi Hitam Murni', 4, 3, 0, 0);

        $this->assertSame($firstMilkFinish, $firstMilkOrder->refresh()->wfq_finish_time);
        $this->assertGreaterThan($firstMilkFinish, $blackOrder->refresh()->wfq_finish_time);
    }

    public function test_next_order_claims_the_lowest_wfq_finish_time(): void
    {
        $user = $this->createTestUser();
        $firstMilkOrder = $this->createTestOrder($user, 'Kopi Susu Saja', 2, 3, 0, 3);
        $this->createTestOrder($user, 'Kopi Hitam Murni', 4, 3, 0, 0);

        $this->withToken('test-device-token')
            ->getJson('/api/next-order')
            ->assertOk()
            ->assertJsonPath('0.id', $firstMilkOrder->id)
            ->assertJsonPath('0.status', 'proses')
            ->assertJsonPath('0.service_time', 11);

        $this->assertSame('proses', $firstMilkOrder->refresh()->status);
    }

    public function test_new_cloud_orders_are_not_scheduled_until_the_edge_or_fallback_claims_them(): void
    {
        config(['order_nodes.role' => 'cloud']);
        $user = $this->createTestUser();

        $this->actingAs($user)
            ->post('/orders', [
                'menu_name' => 'Kopi Susu',
                'weight' => 2,
            ])
            ->assertRedirect();

        $this->assertNull(Order::query()->latest('id')->value('wfq_finish_time'));
    }

    public function test_esp32_can_update_an_order_status(): void
    {
        $user = $this->createTestUser();
        $order = $this->createTestOrder($user, 'Kopi Susu Saja', 2, 3, 0, 3);

        $this->withToken('test-device-token')->getJson('/api/next-order')->assertOk();

        $this->withToken('test-device-token')->postJson("/api/update-status/{$order->id}", [
            'status' => 'proses',
        ])->assertOk();

        $this->withToken('test-device-token')->postJson("/api/update-status/{$order->id}", [
            'status' => 'success',
        ])->assertOk()->assertJson(['message' => 'success']);

        $order->refresh();

        $this->assertSame('success', $order->status);
        $this->assertNotNull($order->started_at);
        $this->assertNotNull($order->completed_at);
        $this->assertNotNull($order->processingTime());
        $this->assertNotNull($order->flowTime());
    }

    public function test_edge_sync_endpoints_require_the_shared_token(): void
    {
        config([
            'order_nodes.role' => 'cloud',
            'order_nodes.shared_token' => 'test-edge-token',
        ]);

        $this->getJson('/api/edge/orders')->assertUnauthorized();
        $this->postJson('/api/edge/orders/1/claim', ['node_id' => 'edge-1'])->assertUnauthorized();
    }

    public function test_esp32_endpoints_require_a_device_token(): void
    {
        $this->getJson('/api/next-order')->assertUnauthorized();
    }

    public function test_cloud_atomically_claims_orders_for_an_edge_node(): void
    {
        config([
            'order_nodes.role' => 'cloud',
            'order_nodes.shared_token' => 'test-edge-token',
        ]);
        $user = $this->createTestUser();
        $order = $this->createTestOrder($user, 'Kopi Susu Saja', 2, 3, 0, 3);

        $this->withToken('test-edge-token')
            ->postJson("/api/edge/orders/{$order->id}/schedule", [
                'wfq_finish_time' => 5.5,
            ])
            ->assertOk();
        $this->assertSame('5.50000000', $order->refresh()->wfq_finish_time);

        $this->withToken('test-edge-token')
            ->postJson("/api/edge/orders/{$order->id}/claim", ['node_id' => 'edge-1'])
            ->assertOk()
            ->assertJsonPath('order.id', $order->id);

        $this->assertSame('edge:edge-1', $order->refresh()->processing_node);
        $this->assertNull($order->started_at);
        $this->withToken('test-edge-token')
            ->postJson("/api/edge/orders/{$order->id}/status", [
                'node_id' => 'edge-1',
                'status' => 'proses',
            ])
            ->assertOk();
        $this->assertNotNull($order->refresh()->started_at);
        $this->withToken('test-edge-token')
            ->postJson("/api/edge/orders/{$order->id}/status", [
                'node_id' => 'edge-1',
                'status' => 'success',
            ])
            ->assertOk();
        $this->assertNotNull($order->refresh()->completed_at);
        $this->withToken('test-device-token')
            ->getJson('/api/next-order')
            ->assertOk()
            ->assertExactJson([]);

        $this->withToken('test-edge-token')
            ->postJson("/api/edge/orders/{$order->id}/claim", ['node_id' => 'edge-2'])
            ->assertStatus(409);
    }

    public function test_edge_synchronizes_and_selects_the_lowest_local_wfq_finish_time(): void
    {
        config([
            'order_nodes.role' => 'edge',
            'order_nodes.id' => 'edge-1',
            'order_nodes.cloud_url' => 'https://cloud.test',
            'order_nodes.shared_token' => 'test-edge-token',
        ]);

        $claimedOrderIds = [];
        Http::fake(function ($request) use (&$claimedOrderIds) {
            if ($request->url() === 'https://cloud.test/api/edge/orders') {
                return Http::response([
                    [
                        'id' => 41,
                        'user_name' => 'Milk Customer',
                        'menu_name' => 'Kopi Susu',
                        'weight' => 2,
                        'pump_kopi' => 3,
                        'pump_gula' => 0,
                        'pump_susu' => 3,
                        'pump_air' => 5,
                    ],
                    [
                        'id' => 42,
                        'user_name' => 'Coffee Customer',
                        'menu_name' => 'Kopi Hitam',
                        'weight' => 1,
                        'pump_kopi' => 3,
                        'pump_gula' => 0,
                        'pump_susu' => 0,
                        'pump_air' => 5,
                    ],
                    [
                        'id' => 43,
                        'user_name' => 'Espresso Customer',
                        'menu_name' => 'Espresso',
                        'weight' => 1,
                        'pump_kopi' => 0,
                        'pump_gula' => 0,
                        'pump_susu' => 0,
                        'pump_air' => 1,
                    ],
                ]);
            }

            if (str_ends_with($request->url(), '/claim')) {
                $claimedOrderId = (int) basename(str_replace('/claim', '', $request->url()));
                $claimedOrderIds[] = $claimedOrderId;

                return Http::response(['order' => ['id' => $claimedOrderId]]);
            }

            if (str_ends_with($request->url(), '/schedule')) {
                return Http::response(['message' => 'success']);
            }

            return Http::response(['message' => 'unexpected request'], 404);
        });

        $edgeOrders = app(\App\Services\EdgeOrderService::class);
        $orders = $edgeOrders->claimNextOrder();
        $nextOrders = $edgeOrders->claimNextOrder();

        $this->assertSame(41, $orders[0]['id']);
        $this->assertSame(43, $nextOrders[0]['id']);
        $this->assertSame([41, 43], $claimedOrderIds);
        $this->assertSame(
            'proses',
            \App\Models\EdgeOrder::query()->where('cloud_order_id', 43)->value('status'),
        );
        $this->assertSame(
            'pending',
            \App\Models\EdgeOrder::query()->where('cloud_order_id', 42)->value('status'),
        );
        $this->assertSame(6.5, $nextOrders[0]['wfq_finish_time']);
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer test-edge-token'));
    }

    private function createTestOrder(User $user, string $menuName, int $weight, int $coffee, int $sugar, int $milk): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'menu_name' => $menuName,
            'weight' => $weight,
            'p_kopi' => $coffee,
            'p_gula' => $sugar,
            'p_susu' => $milk,
            'p_air' => 5,
            'status' => 'pending',
        ]);

        return app(WfqScheduler::class)->assignFinishTime($order);
    }

    private function createTestUser(): User
    {
        return User::create([
            'name' => 'WFQ Test User',
            'email' => fake()->unique()->safeEmail(),
            'role' => 'customer',
            'password' => Hash::make('password'),
        ]);
    }
}