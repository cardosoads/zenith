<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('booking_customer_fields')->delete();
            DB::table('booking_payments')->delete();
            DB::table('bookings')->delete();
            DB::table('unavailabilities')->delete();
            DB::table('availability_rules')->delete();
            DB::table('services')->delete();
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->foreignId('provider_agenda_id')->nullable()->after('provider_profile_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('availability_rules', function (Blueprint $table): void {
            $table->foreignId('provider_agenda_id')->nullable()->after('provider_profile_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('provider_agenda_id')->nullable()->after('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->index(['provider_agenda_id', 'starts_at']);
        });

        Schema::table('unavailabilities', function (Blueprint $table): void {
            $table->foreignId('provider_agenda_id')->nullable()->after('provider_profile_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unavailabilities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('provider_agenda_id');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex(['provider_agenda_id', 'starts_at']);
            $table->dropConstrainedForeignId('provider_agenda_id');
        });

        Schema::table('availability_rules', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('provider_agenda_id');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('provider_agenda_id');
        });
    }
};
