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
        Schema::create('wish_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('wishlistable_type'); // 'App\Models\Car', 'App\Models\Property', 'App\Models\Hotel'
            $table->unsignedBigInteger('wishlistable_id');
            $table->timestamps();

            $table->index(['wishlistable_type', 'wishlistable_id']);
            $table->unique(['user_id', 'wishlistable_type', 'wishlistable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wish_lists');
    }
};
