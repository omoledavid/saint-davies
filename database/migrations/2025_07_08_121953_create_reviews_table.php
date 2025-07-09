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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reviewable_type'); // 'App\Models\Car', 'App\Models\Property', 'App\Models\Hotel'
            $table->unsignedBigInteger('reviewable_id');
            $table->string('service_type'); // 'hire', 'purchase', 'rent', 'accommodation'
            $table->tinyInteger('rating')->unsigned(); // 1-5 stars
            $table->text('comment')->nullable();
            $table->boolean('is_verified')->default(false); // if user actually used the service
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id']);
            $table->index(['user_id', 'service_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
