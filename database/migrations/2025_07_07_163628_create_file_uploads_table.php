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
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('file_type')->nullable(); // e.g. image/png, video/mp4
            $table->string('file_name')->nullable(); // original filename
            $table->boolean('is_main')->default(false);
            $table->unsignedInteger('order')->nullable();

            // Polymorphic relation
            $table->morphs('uploadable'); // adds uploadable_id and uploadable_type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
