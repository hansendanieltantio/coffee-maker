<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edge_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cloud_order_id')->unique();
            $table->string('user_name');
            $table->string('menu_name');
            $table->integer('weight');
            $table->integer('p_kopi')->default(0);
            $table->integer('p_gula')->default(0);
            $table->integer('p_susu')->default(0);
            $table->integer('p_air')->default(0);
            $table->string('status')->default('pending');
            $table->decimal('wfq_finish_time', 20, 8)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cloud_synced_at')->nullable();
            $table->timestamp('wfq_synced_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'wfq_finish_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edge_orders');
    }
};
