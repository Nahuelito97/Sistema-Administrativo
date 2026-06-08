<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question', 500);
            $table->string('answer', 1000)->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
