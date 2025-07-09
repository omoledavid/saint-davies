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
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // the guest
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade'); // the hotel
            $table->foreignId('room_id')->constrained('hotel_rooms')->onDelete('cascade'); // the specific room

            // Booking details
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_guests')->default(1);

            // Optional: Add-ons or special requests
            $table->text('special_requests')->nullable();

            // Booking status
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out'])->default('pending');

            // Payment tracking
            $table->boolean('is_paid')->default(false);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('paystack_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_bookings');
    }
};
