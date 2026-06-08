<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->index()->after('user_id');
            $table->unsignedBigInteger('seller_id')->nullable()->index()->after('company_id');
        });

        // Backfill: órdenes existentes quedan bajo la tienda por defecto (Ordasi).
        $default = DB::table('companies')->where('slug', 'ordasi')->value('id');
        if ($default) {
            DB::table('orders')->whereNull('company_id')->update(['company_id' => $default]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'seller_id']);
        });
    }
};
