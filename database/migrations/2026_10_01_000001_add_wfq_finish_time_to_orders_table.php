<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('wfq_finish_time', 20, 8)->nullable()->after('status');
            $table->index(['status', 'wfq_finish_time']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['status', 'wfq_finish_time']);
            $table->dropColumn('wfq_finish_time');
        });
    }
};