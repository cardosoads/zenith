<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('provider_agendas', function (Blueprint $table) {
            $table->string('primary_color')->default('#18181b')->after('preset');
            $table->string('secondary_color')->default('#27272a')->after('primary_color');
        });
    }

    public function down(): void
    {
        Schema::table('provider_agendas', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color']);
        });
    }
};
