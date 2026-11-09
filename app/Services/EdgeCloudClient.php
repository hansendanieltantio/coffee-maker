<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LogicException;

class EdgeCloudClient
{
    public function pendingOrders(): array
    {
        $orders = $this->request()
            ->get($this->cloudUrl().'/api/edge/orders')
            ->throw()
            ->json();

        if (! is_array($orders)) {
            throw new \UnexpectedValueException('Cloud returned an invalid pending-order response.');
        }

        return $orders;
    }

    public function claimOrder(int $orderId): ?array
    {
        $response = $this->request()->post(
            $this->cloudUrl()."/api/edge/orders/{$orderId}/claim",
            ['node_id' => config('order_nodes.id')],
        );

        if (in_array($response->status(), [404, 409], true)) {
            return null;
        }

        return $this->orderFromResponse($response);
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        $this->request()
            ->post($this->cloudUrl()."/api/edge/orders/{$orderId}/status", [
                'node_id' => config('order_nodes.id'),
                'status' => $status,
            ])
            ->throw();
    }

    public function updateFinishTime(int $orderId, float $finishTime): bool
    {
        $response = $this->request()->post(
            $this->cloudUrl()."/api/edge/orders/{$orderId}/schedule",
            ['wfq_finish_time' => $finishTime],
        );

        if ($response->status() === 409) {
            return false;
        }

        $response->throw();

        return true;
    }

    private function request()
    {
        $token = config('order_nodes.shared_token');

        if (! is_string($token) || $token === '') {
            throw new LogicException('ORDER_NODE_TOKEN must be configured on the edge node.');
        }

        return Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->connectTimeout(3)
            ->timeout(8);
    }

    private function cloudUrl(): string
    {
        $url = config('order_nodes.cloud_url');

        if (! is_string($url) || trim($url) === '') {
            throw new LogicException('ORDER_CLOUD_URL must be configured on the edge node.');
        }

        return rtrim($url, '/');
    }

    private function orderFromResponse(Response $response): array
    {
        $payload = $response->throw()->json('order');

        if (! is_array($payload)) {
            throw new \UnexpectedValueException('Cloud returned an invalid claimed-order response.');
        }

        return $payload;
    }
}
