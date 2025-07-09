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
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('tenant_number')->unique();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('phone');
            $table->string('marital_status')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('occupation')->nullable();
            $table->string('income')->nullable();
            $table->string('id_number')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_front_image')->nullable();
            $table->string('id_back_image')->nullable();
            $table->string('user_image')->nullable();
            $table->foreignId('property_unit_id')->constrained('property_units')->onDelete('cascade');
            $table->date('rent_start')->nullable();
            $table->date('rent_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
