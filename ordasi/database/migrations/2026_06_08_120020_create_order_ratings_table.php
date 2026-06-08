<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Calificación de la experiencia de compra (una por orden entregada).
        Schema::create('order_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('product_score');    // calidad del producto
            $table->unsignedTinyInteger('attention_score');  // atención del vendedor
            $table->unsignedTinyInteger('shipping_score');   // envío
            $table->string('comment', 1000)->nullable();
            $table->timestamps();
            $table->unique('order_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_ratings');
    }
};
