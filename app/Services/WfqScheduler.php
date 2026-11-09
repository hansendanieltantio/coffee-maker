<?php

namespace App\Services;

use App\Models\Order;
use App\Models\EdgeOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WfqScheduler
{
    public function assignFinishTime(Order|EdgeOrder $order): Order|EdgeOrder
    {
        if ($order->wfq_finish_time !== null) {
            return $order;
        }

        $serviceTime = $order->serviceTime();
        $weight = max((int) $order->weight, 1);
        $orderModel = $order::class;
        $lastFlowFinish = (float) ($orderModel::query()
            ->where('menu_name', $order->menu_name)
            ->whereNotNull('wfq_finish_time')
            ->max('wfq_finish_time') ?? 0);
        $virtualTime = (float) ($orderModel::query()
            ->whereIn('status', ['pending', 'proses'])
            ->whereNotNull('wfq_finish_time')
            ->min('wfq_finish_time') ?? 0);

        $order->forceFill([
            'wfq_finish_time' => max($lastFlowFinish, $virtualTime) + ($serviceTime / $weight),
        ])->save();

        return $order->refresh();
    }

    public function preparePendingOrders(string $orderModel = Order::class): Collection
    {
        if (! in_array($orderModel, [Order::class, EdgeOrder::class], true)) {
            throw new \InvalidArgumentException('Unsupported order model for WFQ scheduling.');
        }

        $pendingOrders = $orderModel::query()
            ->where('status', 'pending')
            ->whereNull('wfq_finish_time')
            ->orderBy('id')
            ->get();

        foreach ($pendingOrders as $order) {
            $this->assignFinishTime($order);
        }

        return $pendingOrders;
    }

    public function claimNextOrder(): ?Order
    {
        return DB::transaction(function (): ?Order {
            $this->preparePendingOrders();

            $order = Order::query()
                ->where('status', 'pending')
                ->whereNotNull('wfq_finish_time')
                ->orderBy('wfq_finish_time')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($order === null) {
                return null;
            }

            $order->forceFill([
                'status' => 'proses',
                'processing_node' => 'cloud',
            ])->save();

            return $order->refresh();
        });
    }
}