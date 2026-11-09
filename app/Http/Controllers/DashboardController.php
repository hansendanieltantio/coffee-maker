<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $decorateOrders = static function ($orders) {
            return $orders->map(function (Order $order): array {
                return [
                    ...$order->toArray(),
                    'service_time' => $order->serviceTime(),
                    'waiting_time' => $order->waitingTime(),
                    'processing_time' => $order->processingTime(),
                    'flow_time' => $order->flowTime(),
                ];
            });
        };

        $allOrderRecords = collect();
        if ($user->role === 'admin') {
            $allOrderRecords = Order::with('user')->latest()->get();
        }

        $myOrderRecords = Order::where('user_id', $user->id)->latest()->get();
        $visibleOrders = $user->role === 'admin' ? $allOrderRecords : $myOrderRecords;
        $visiblePendingOrders = $visibleOrders
            ->where('status', 'pending')
            ->filter(fn (Order $order): bool => $order->wfq_finish_time !== null)
            ->sortBy(fn (Order $order): array => [$order->wfq_finish_time, $order->id])
            ->values();

        $allOrders = $decorateOrders($allOrderRecords);
        $myOrders = $decorateOrders($myOrderRecords);

        $queueSummary = [
            'pending' => $visibleOrders->where('status', 'pending')->count(),
            'proses' => $visibleOrders->where('status', 'proses')->count(),
            'success' => $visibleOrders->where('status', 'success')->count(),
            'next_order' => $visiblePendingOrders->first()?->only(['id', 'menu_name', 'wfq_finish_time']),
        ];
        // 2. Daftar Menu (Pastikan dikirim ke frontend!)
        $coffeeMenus = [
            ['id' => 4, 'name' => 'Kopi Hitam + Gula', 'weight' => 8, 'desc' => 'Energi instan dengan rasa berkarakter.'],
            ['id' => 3, 'name' => 'Kopi Hitam Murni', 'weight' => 10, 'desc' => 'Sajian murni bagi pecinta kopi sejati.'],
            ['id' => 2, 'name' => 'Kopi Susu Creamy', 'weight' => 5, 'desc' => 'Lembut dan manis di setiap tegukan.'],
            ['id' => 1, 'name' => 'Kopi Susu Fit', 'weight' => 7, 'desc' => 'Kombinasi sehat kopi dan susu gurih.'],
        ];

        return Inertia::render('Dashboard', [
            'auth' => [
                'user' => $user,
            ],
            'orders' => $allOrders,
            'my_orders' => $myOrders,
            'queue_summary' => $queueSummary,
            'coffeeMenus' => $coffeeMenus, // INI WAJIB ADA
        ]);
    }
}