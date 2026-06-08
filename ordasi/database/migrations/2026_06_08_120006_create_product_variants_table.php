<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');                 // ej. "Talle M / Azul"
            $table->json('attributes')->nullable(); // ej. {"talle":"M","color":"azul"}
            $table->string('sku', 60)->nullable();
            $table->decimal('price', 12, 2);        // precio de venta de esta variante
            $table->integer('stock')->default(0);
            $table->timestamps();
            $table->index('product_id');
        });

        // El detalle de carrito y de orden pueden referir a una variante.
        Schema::table('shopping_cart_details', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });
        Schema::table('order_details', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('variant_name')->nullable()->after('product_variant_id'); // snapshot
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn('variant_name');
        });
        Schema::table('shopping_cart_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
        Schema::dropIfExists('product_variants');
    }
};
