<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('pix_key_type')->nullable()->after('pix_key');
            $table->string('pix_holder_name')->nullable()->after('pix_key_type');
            $table->string('pix_holder_document')->nullable()->after('pix_holder_name');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['pix_key_type', 'pix_holder_name', 'pix_holder_document']);
        });
    }
};
