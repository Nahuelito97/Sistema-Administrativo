<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('promotion_type', ['percent', 'fixed_amount'])->default('percent');
            $table->dateTime('start_date');
            $table->dateTime('ending_date');
            $table->decimal('discount_rate', 5, 2)->nullable();        // % (0-100) si percent
            $table->decimal('fixed_amount_discount', 12, 2)->nullable(); // monto fijo si fixed_amount
            $table->timestamps();
        });

        Schema::create('product_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
        Schema::dropIfExists('promotions');
    }
};
