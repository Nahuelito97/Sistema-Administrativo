<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_carrier')->nullable()->after('shipping_address');
            $table->string('tracking_code')->nullable()->after('shipping_carrier');
            $table->timestamp('shipped_at')->nullable()->after('tracking_code');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_carrier', 'tracking_code', 'shipped_at', 'delivered_at']);
        });
    }
};
