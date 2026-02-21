<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('theme')->default('auto');
            $table->string('accent')->default('sky');
            $table->string('density')->default('medium');
            $table->string('preset')->default('clean');
            $table->json('customer_extra_fields')->nullable();
            $table->unsignedSmallInteger('embed_height')->default(680);
            $table->timestamps();

            $table->unique(['provider_profile_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_agendas');
    }
};
