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
        Schema::table('provider_agendas', function (Blueprint $table) {
            $table->integer('embed_width')->default(480)->after('embed_height');
            $table->boolean('transparent_bg')->default(false)->after('embed_width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_agendas', function (Blueprint $table) {
            //
        });
    }
};
