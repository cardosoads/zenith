<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('public_token')->unique();
            $table->string('theme')->default('auto');
            $table->string('accent')->default('sky');
            $table->string('density')->default('medium');
            $table->json('customer_extra_fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_configs');
    }
};
