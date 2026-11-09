<?php

namespace App\Http\Controllers;

use App\Services\EdgeOrderService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LogicException;
use UnexpectedValueException;

class EdgeOrderController extends Controller
{
    public function nextOrder(EdgeOrderService $edgeOrders): JsonResponse
    {
        try {
            return response()->json($edgeOrders->claimNextOrder());
        } catch (ConnectionException|RequestException|LogicException|UnexpectedValueException $exception) {
            Log::error('Edge gagal mengambil atau mengklaim order dari cloud.', [
                'exception' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'cloud synchronization failed'], 503);
        }
    }

    public function updateStatus(Request $request, int $id, EdgeOrderService $edgeOrders): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:proses,success'],
        ]);

        try {
            if (! $edgeOrders->updateStatus($id, $validated['status'])) {
                return response()->json(['message' => 'not found on edge'], 404);
            }

            return response()->json(['message' => 'success']);
        } catch (ConnectionException|RequestException|LogicException $exception) {
            Log::error('Edge gagal menyinkronkan status order ke cloud.', [
                'order_id' => $id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'cloud status synchronization failed'], 503);
        }
    }
}
