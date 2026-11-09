<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('started_at')->nullable()->after('created_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->index(['status', 'started_at']);
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['status', 'started_at']);
            $table->dropIndex(['completed_at']);
            $table->dropColumn(['started_at', 'completed_at']);
        });
    }
};