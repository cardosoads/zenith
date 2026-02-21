<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('widget_configs')) {
            return;
        }

        Schema::create('widget_configs_new', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_agenda_id')->constrained()->cascadeOnDelete();
            $table->string('public_token')->unique();
            $table->string('theme')->default('auto');
            $table->string('accent')->default('sky');
            $table->string('density')->default('medium');
            $table->json('customer_extra_fields')->nullable();
            $table->timestamps();

            $table->unique('provider_agenda_id');
        });

        DB::table('widget_configs')->delete();

        Schema::drop('widget_configs');
        Schema::rename('widget_configs_new', 'widget_configs');
    }

    public function down(): void
    {
        if (! Schema::hasTable('widget_configs')) {
            return;
        }

        Schema::create('widget_configs_old', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('public_token')->unique();
            $table->string('theme')->default('auto');
            $table->string('accent')->default('sky');
            $table->string('density')->default('medium');
            $table->json('customer_extra_fields')->nullable();
            $table->timestamps();
        });

        DB::table('widget_configs')->delete();

        Schema::drop('widget_configs');
        Schema::rename('widget_configs_old', 'widget_configs');
    }
};
