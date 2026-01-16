<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('province'); 
            $table->string('city'); 
            $table->json('features')->nullable(); 
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['available', 'booked'])->default('available');
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};