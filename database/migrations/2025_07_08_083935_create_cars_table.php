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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade'); // landlord, dealer, or user
            $table->string('title'); // e.g. "2017 Toyota Camry SE"
            $table->string('make'); // e.g. Toyota
            $table->string('model'); // e.g. Camry
            $table->string('year')->nullable();
            $table->string('condition')->nullable();//new or used
            $table->string('transmission')->nullable(); // e.g. automatic, manual
            $table->string('fuel_type')->nullable(); // petrol, diesel, electric
            $table->decimal('price', 12, 2); // for sale or rent
            $table->enum('type', ['rent', 'sale'])->default('sale');
            $table->enum('rent_frequency', ['daily', 'weekly', 'monthly'])->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
