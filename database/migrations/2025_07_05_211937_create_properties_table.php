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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('rent_price', 12, 2)->nullable();
            $table->enum('rent_frequency', ['monthly', 'yearly', 'weekly', 'quarterly'])->nullable();
            $table->string('image')->nullable();
            $table->string('sop')->nullable();// size of property
            $table->string('video')->nullable();
            $table->string('status')->default('pending');
            $table->string('property_type')->nullable();
            $table->string('property_category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
