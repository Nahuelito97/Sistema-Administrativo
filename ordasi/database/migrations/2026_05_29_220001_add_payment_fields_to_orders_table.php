<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_platform')->nullable()->after('payment_status');
            $table->string('preference_id')->nullable()->after('payment_platform');
            $table->string('payment_id')->nullable()->after('preference_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_platform', 'preference_id', 'payment_id']);
        });
    }
};
