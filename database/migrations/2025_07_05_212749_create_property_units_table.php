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
        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('rent_amount', 10, 2)->nullable();
            $table->enum('rent_frequency', ['monthly', 'yearly', 'weekly', 'quarterly'])->nullable();
            $table->boolean('is_occupied')->default(false);
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('agreement_file')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->integer('bed_room')->default(1)->nullable();
            $table->integer('bath_room')->default(1)->nullable();
            $table->boolean('parking')->default(false)->nullable();
            $table->boolean('security')->default(false)->nullable();
            $table->boolean('water')->default(false)->nullable();
            $table->boolean('electricity')->default(false)->nullable();
            $table->boolean('internet')->default(false)->nullable();
            $table->boolean('tv')->default(false)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_units');
    }
};
