<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Definición de características por categoría (ej. Electrónica → "Marca", "Potencia").
        Schema::create('caracteristics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // Valor que un producto le da a una característica.
        Schema::create('product_caracteristic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('caracteristic_id')->constrained()->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->unique(['product_id', 'caracteristic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_caracteristic');
        Schema::dropIfExists('caracteristics');
    }
};
