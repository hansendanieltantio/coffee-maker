<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EdgeCloudController extends Controller
{
    public function pendingOrders(Request $request): JsonResponse
    {
        $this->authorizeEdgeNode($request);

        return response()->json(Order::query()
            ->where('status', 'pending')
            ->orderBy('id')
            ->get()
            ->map(fn (Order $order): array => $this->serializeOrder($order))
            ->all());
    }

    public function claim(Request $request, int $id): JsonResponse
    {
        $this->authorizeEdgeNode($request);
        $validated = $request->validate([
            'node_id' => ['required', 'string', 'max:100'],
        ]);
        $processingNode = 'edge:'.$validated['node_id'];

        $order = DB::transaction(function () use ($id, $processingNode): ?Order {
            $order = Order::query()->lockForUpdate()->find($id);

            if ($order === null) {
                return null;
            }

            if ($order->status === 'proses' && $order->processing_node === $processingNode) {
                return $order;
            }

            if ($order->status !== 'pending') {
                return null;
            }

            $order->forceFill([
                'status' => 'proses',
                'processing_node' => $processingNode,
            ])->save();

            return $order->refresh();
        });

        if ($order === null) {
            return response()->json(['message' => 'order is no longer pending'], 409);
        }

        return response()->json(['order' => $this->serializeOrder($order)]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeEdgeNode($request);
        $validated = $request->validate([
            'node_id' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:proses,success'],
        ]);

        $order = Order::query()->find($id);

        if ($order === null) {
            return response()->json(['message' => 'not found'], 404);
        }

        if ($order->processing_node !== 'edge:'.$validated['node_id']) {
            return response()->json(['message' => 'order is not owned by this edge node'], 409);
        }

        if ($validated['status'] === 'success' && $order->status !== 'proses' && $order->status !== 'success') {
            return response()->json(['message' => 'invalid order status transition'], 409);
        }

        if ($order->status !== 'success') {
            $order->status = $validated['status'];

            if ($validated['status'] === 'proses' && $order->started_at === null) {
                $order->started_at = now();
            }

            if ($validated['status'] === 'success' && $order->completed_at === null) {
                $order->completed_at = now();
            }

            $order->save();
        }

        return response()->json(['message' => 'success']);
    }

    public function updateSchedule(Request $request, int $id): JsonResponse
    {
        $this->authorizeEdgeNode($request);
        $validated = $request->validate([
            'wfq_finish_time' => ['required', 'numeric', 'min:0'],
        ]);

        $order = Order::query()->find($id);

        if ($order === null || $order->status !== 'pending') {
            return response()->json(['message' => 'order is no longer pending'], 409);
        }

        $order->forceFill([
            'wfq_finish_time' => $validated['wfq_finish_time'],
        ])->save();

        return response()->json(['message' => 'success']);
    }

    private function authorizeEdgeNode(Request $request): void
    {
        abort_unless(config('order_nodes.role') === 'cloud', 503, 'Edge synchronization endpoints are only available on the cloud node.');

        $token = config('order_nodes.shared_token');
        abort_unless(
            is_string($token)
                && $token !== ''
                && is_string($request->bearerToken())
                && hash_equals($token, $request->bearerToken()),
            401,
            'Invalid edge node credentials.',
        );
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id' => (int) $order->id,
            'user_name' => (string) $order->user_name,
            'menu_name' => (string) $order->menu_name,
            'weight' => (int) $order->weight,
            'pump_kopi' => (int) $order->p_kopi,
            'pump_gula' => (int) $order->p_gula,
            'pump_susu' => (int) $order->p_susu,
            'pump_air' => (int) $order->p_air,
            'status' => $order->status,
        ];
    }
}
