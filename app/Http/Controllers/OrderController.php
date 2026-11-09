<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\EdgeOrderService;
use App\Services\WfqScheduler;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'orders' => Order::orderBy('id', 'desc')->get(),
            'my_orders' => Order::where('user_id', auth()->id())
                ->orderBy('id', 'desc')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(config('order_nodes.role') === 'cloud', 503, 'Order intake is only available on the cloud node.');

        $request->validate([
            'menu_name' => 'required|string',
            'weight' => 'required|integer',
        ]);

        // 1. Normalisasi: Kecilkan semua huruf dan hapus spasi berlebih
        $menu = strtolower(trim($request->menu_name));
        Log::info("Memproses menu: " . $menu);

        // 2. Inisialisasi Durasi
        $dk = 0; $dg = 0; $ds = 0; $da = 5; // Air default 5 detik

        // 3. Pengecekan Kata Kunci secara Agresif
        if (str_contains($menu, 'kopi')) {
            $dk = 3;
            Log::info("Kopi terdeteksi (3s)");
        }
        
        if (str_contains($menu, 'gula')) {
            $dg = 2;
            Log::info("Gula terdeteksi (2s)");
        }

        if (str_contains($menu, 'susu')) {
            $ds = 3;
            Log::info("Susu terdeteksi (3s)");
        }

        // 4. Simpan ke Database
        // Pastikan kolom pump_kopi dll di database tipenya integer
        $order = Order::create([
            'user_id'   => auth()->id(),
            'user_name' => auth()->user()->name,
            'menu_name' => $request->menu_name,
            'weight'    => (int)$request->weight,
            'status'    => 'pending',
            'p_kopi'    => $dk, // Gunakan p_kopi sesuai model
            'p_gula'    => $dg,
            'p_susu'    => $ds,
            'p_air'     => $da,
        ]);

        return redirect()->back();
    }

    public function getNextOrder(WfqScheduler $scheduler, EdgeOrderService $edgeOrders): JsonResponse
    {
        if (config('order_nodes.role') === 'edge') {
            return response()->json($edgeOrders->claimNextOrder());
        }

        $order = $scheduler->claimNextOrder();

        if ($order === null) {
            return response()->json([]);
        }

        Log::info('ESP32 mengambil order WFQ.', ['order_id' => $order->id]);

        return response()->json([[
            'id' => (int) $order->id,
            'user_name' => (string) $order->user_name,
            'menu_name' => (string) $order->menu_name,
            'weight' => (int) $order->weight,
            'service_time' => $order->serviceTime(),
            'pump_kopi' => (int) $order->p_kopi,
            'pump_gula' => (int) $order->p_gula,
            'pump_susu' => (int) $order->p_susu,
            'pump_air' => (int) $order->p_air,
            'status' => $order->status,
            'wfq_finish_time' => (float) $order->wfq_finish_time,
        ]]);
    }

    public function updateStatus(Request $request, int $id, EdgeOrderService $edgeOrders): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:proses,success,pending'],
        ]);

        if (config('order_nodes.role') === 'edge') {
            if (! $edgeOrders->updateStatus($id, $validated['status'])) {
                return response()->json(['message' => 'not found on edge'], 404);
            }

            return response()->json(['message' => 'success']);
        }

        $order = Order::find($id);

        if ($order) {
            $order->status = $validated['status'];

            if ($validated['status'] === 'proses' && $order->started_at === null) {
                $order->started_at = now();
            }

            if ($validated['status'] === 'success' && $order->completed_at === null) {
                $order->completed_at = now();
            }

            $order->save();
            return response()->json(['message' => 'success']);
        }

        return response()->json(['message' => 'not found'], 404);
    }

    public function checkQueue(int $id)
    {
        $order = Order::find($id);

        if ($order === null) {
            return response()->json(['message' => 'not found'], 404);
        }

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
        ]);
    }
}