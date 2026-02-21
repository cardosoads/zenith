<?php

use App\BookingSource;
use App\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->string('status')->default(BookingStatus::Pending->value)->index();
            $table->string('source')->default(BookingSource::Widget->value);
            $table->timestamps();

            $table->index(['provider_profile_id', 'starts_at']);
            $table->unique(['provider_profile_id', 'starts_at', 'ends_at', 'status'], 'bookings_provider_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
