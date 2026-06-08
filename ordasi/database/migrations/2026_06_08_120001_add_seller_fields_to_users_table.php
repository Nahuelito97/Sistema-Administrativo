<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Estado del vendedor: 1=INACTIVE, 2=PENDING, 3=ACTIVE, 4=BANNED.
            $table->unsignedTinyInteger('status_seller_id')->default(1)->index()->after('company_id');
            // KYC del vendedor.
            $table->string('dni')->nullable()->after('status_seller_id');
            $table->string('dni_front')->nullable()->after('dni');
            $table->string('dni_back')->nullable()->after('dni_front');
            $table->string('selfie')->nullable()->after('dni_back');
            $table->string('cbu', 30)->nullable()->after('selfie');
            $table->date('seller_since')->nullable()->after('cbu');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status_seller_id', 'dni', 'dni_front', 'dni_back', 'selfie', 'cbu', 'seller_since']);
        });
    }
};
