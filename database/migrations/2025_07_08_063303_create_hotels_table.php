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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade'); // Landlord or hotel manager
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('video_url')->nullable();
            $table->string('country')->default('Nigeria');
            $table->decimal('price', 10, 2)->nullable();
            $table->longText('policies')->nullable();
            $table->longText('cancellation_policy')->nullable();
            $table->string('check_in_time')->nullable();
            $table->string('amenities')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
