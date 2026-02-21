<?php

use App\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('method')->default('pix');
            $table->string('provider')->default('asaas');
            $table->string('external_id')->nullable()->index();
            $table->text('qr_code_text')->nullable();
            $table->text('qr_code_image_url')->nullable();
            $table->string('status')->default(PaymentStatus::Pending->value)->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
