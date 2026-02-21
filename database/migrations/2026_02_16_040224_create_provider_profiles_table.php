<?php

use App\ProviderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->string('status')->default(ProviderStatus::Pending->value);
            $table->string('billing_status')->default('pending');
            $table->unsignedTinyInteger('cancellation_cutoff_hours')->default(24);
            $table->string('pix_key')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
