<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // promotion_type ahora admite: percent, fixed_amount, combo, wholesale.
            $table->unsignedSmallInteger('combo_buy')->nullable()->after('fixed_amount_discount');   // "comprá N"
            $table->unsignedSmallInteger('combo_pay')->nullable()->after('combo_buy');                // "pagá M"
            $table->unsignedSmallInteger('wholesale_min_qty')->nullable()->after('combo_pay');        // desde X unidades
            $table->decimal('wholesale_price', 12, 2)->nullable()->after('wholesale_min_qty');        // precio mayorista
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['combo_buy', 'combo_pay', 'wholesale_min_qty', 'wholesale_price']);
        });
    }
};
