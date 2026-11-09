<?php

namespace App\Services;

use App\Models\EdgeOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;

class EdgeOrderService
{
    public function __construct(
        private readonly EdgeCloudClient $cloud,
        private readonly WfqScheduler $scheduler,
    ) {}

    public function syncPendingOrders(): int
    {
        $synced = 0;

        foreach ($this->cloud->pendingOrders() as $remoteOrder) {
            if (! is_array($remoteOrder) || ! isset($remoteOrder['id'])) {
                throw new UnexpectedValueException('Cloud returned a malformed order.');
            }

            $order = EdgeOrder::firstOrNew([
                'cloud_order_id' => (int) $remoteOrder['id'],
            ]);

            if (! $order->exists || $order->status === 'pending') {
                $order->fill([
                    'user_name' => (string) ($remoteOrder['user_name'] ?? 'Customer'),
                    'menu_name' => (string) ($remoteOrder['menu_name'] ?? ''),
                    'weight' => (int) ($remoteOrder['weight'] ?? 1),
                    'p_kopi' => (int) ($remoteOrder['pump_kopi'] ?? 0),
                    'p_gula' => (int) ($remoteOrder['pump_gula'] ?? 0),
                    'p_susu' => (int) ($remoteOrder['pump_susu'] ?? 0),
                    'p_air' => (int) ($remoteOrder['pump_air'] ?? 0),
                    'status' => 'pending',
                ]);
                $order->save();
                $this->scheduler->assignFinishTime($order);
                $synced++;
            }
        }

        return $synced;
    }

    public function syncStatuses(): int
    {
        $synced = 0;

        EdgeOrder::query()
            ->whereIn('status', ['proses', 'success'])
            ->whereNull('cloud_synced_at')
            ->orderBy('id')
            ->get()
            ->each(function (EdgeOrder $order) use (&$synced): void {
                $this->cloud->updateOrderStatus((int) $order->cloud_order_id, $order->status);
                $order->forceFill(['cloud_synced_at' => now()])->save();
                $synced++;
            });

        return $synced;
    }

    public function syncWfqFinishTimes(): int
    {
        $synced = 0;

        EdgeOrder::query()
            ->where('status', 'pending')
            ->whereNotNull('wfq_finish_time')
            ->whereNull('wfq_synced_at')
            ->orderBy('id')
            ->get()
            ->each(function (EdgeOrder $order) use (&$synced): void {
                $syncedToCloud = $this->cloud->updateFinishTime(
                    (int) $order->cloud_order_id,
                    (float) $order->wfq_finish_time,
                );

                if ($syncedToCloud) {
                    $synced++;
                }

                $order->forceFill(['wfq_synced_at' => now()])->save();
            });

        return $synced;
    }

    public function claimNextOrder(): array
    {
        $this->syncPendingOrders();
        $this->syncWfqFinishTimes();

        while (true) {
            $order = DB::transaction(function (): EdgeOrder|false|null {
                $this->scheduler->preparePendingOrders(EdgeOrder::class);

                $order = EdgeOrder::query()
                    ->where('status', 'pending')
                    ->whereNotNull('wfq_finish_time')
                    ->orderBy('wfq_finish_time')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if ($order === null) {
                    return null;
                }

                if ($this->cloud->claimOrder((int) $order->cloud_order_id) === null) {
                    $order->delete();

                    return false;
                }

                $order->forceFill([
                    'status' => 'proses',
                    'cloud_synced_at' => now(),
                ])->save();

                return $order->refresh();
            });

            if ($order === null) {
                return [];
            }

            if ($order === false) {
                continue;
            }

            Log::info('Edge memilih order dengan WFQ.', [
                'order_id' => $order->cloud_order_id,
                'wfq_finish_time' => $order->wfq_finish_time,
            ]);

            return [$this->serializeOrder($order)];
        }
    }

    public function updateStatus(int $cloudOrderId, string $status): bool
    {
        $order = EdgeOrder::query()
            ->where('cloud_order_id', $cloudOrderId)
            ->first();

        if ($order === null) {
            return false;
        }

        $order->status = $status;

        if ($status === 'proses' && $order->started_at === null) {
            $order->started_at = now();
        }

        if ($status === 'success' && $order->completed_at === null) {
            $order->completed_at = now();
        }

        $order->cloud_synced_at = null;
        $order->save();

        $this->cloud->updateOrderStatus($cloudOrderId, $status);
        $order->forceFill(['cloud_synced_at' => now()])->save();

        return true;
    }

    private function serializeOrder(EdgeOrder $order): array
    {
        return [
            'id' => (int) $order->cloud_order_id,
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
        ];
    }
}
