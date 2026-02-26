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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('email_new_booking')->default(true);
            $table->boolean('email_cancellation')->default(true);
            $table->boolean('email_reminders')->default(true);
            $table->boolean('whatsapp_confirmation')->default(true);
            $table->boolean('whatsapp_reminder_24h')->default(true);
            $table->boolean('whatsapp_cancellation')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
