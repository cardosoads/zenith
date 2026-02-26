<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('cnpj', 18)->nullable()->after('display_name');
            $table->string('address', 255)->nullable()->after('cnpj');
            $table->string('logo_path', 255)->nullable()->after('address');
            $table->string('currency', 3)->default('BRL')->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['cnpj', 'address', 'logo_path', 'currency']);
        });
    }
};
