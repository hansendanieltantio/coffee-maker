<?php

use App\Services\EdgeOrderService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('edge:work {--once}', function (EdgeOrderService $edgeOrders) {
    if (config('order_nodes.role') !== 'edge') {
        $this->error('Set ORDER_NODE_ROLE=edge before starting the edge worker.');

        return Command::FAILURE;
    }

    do {
        try {
            $newOrders = $edgeOrders->syncPendingOrders();
            $updatedSchedules = $edgeOrders->syncWfqFinishTimes();
            $updatedStatuses = $edgeOrders->syncStatuses();

            if ($newOrders > 0 || $updatedSchedules > 0 || $updatedStatuses > 0) {
                $this->info("Synced {$newOrders} order(s), {$updatedSchedules} WFQ score(s), and {$updatedStatuses} status update(s).");
            }
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Edge worker could not reach the cloud.', [
                'exception' => $exception->getMessage(),
            ]);
            $this->warn('Cloud unavailable; will retry.');

            if ($this->option('once')) {
                return Command::FAILURE;
            }
        }

        if ($this->option('once')) {
            return Command::SUCCESS;
        }

        sleep(max(1, (int) config('order_nodes.sync_interval', 2)));
    } while (true);
})->purpose('Continuously synchronize pending orders and statuses with the cloud');
