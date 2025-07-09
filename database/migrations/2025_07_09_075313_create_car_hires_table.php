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
        Schema::create('car_hires', function (Blueprint $table) {
            $table->id();
            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // the customer
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');  // the car

            // Rental details
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable(); // can be different from pickup

            $table->integer('duration_in_days')->nullable(); // optional, can be calculated
            $table->decimal('total_price', 10, 2)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('paystack_reference')->nullable();

            // Optional extras
            $table->boolean('with_driver')->default(false);
            $table->boolean('insurance')->default(false);

            // Status tracking
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'active', 'completed'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_hires');
    }
};
