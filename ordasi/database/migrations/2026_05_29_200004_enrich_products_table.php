<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('name');
            $table->text('short_description')->nullable()->after('image');
            $table->longText('long_description')->nullable()->after('short_description');
            // Visibilidad: dónde se muestra el producto (tienda / POS / ambos / oculto).
            $table->enum('visibility', ['SHOP', 'POS', 'BOTH', 'DISABLED'])->default('BOTH')->after('status');
            $table->integer('views')->default(0)->after('visibility');
            $table->foreignId('brand_id')->nullable()->after('provider_id')->constrained()->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->after('brand_id')->constrained()->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropColumn(['slug', 'short_description', 'long_description', 'visibility', 'views', 'deleted_at']);
        });
    }
};
