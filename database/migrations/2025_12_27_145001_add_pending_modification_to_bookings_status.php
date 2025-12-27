<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status column to include the new values
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'rejected', 'cancelled', 'completed', 'pending_modification')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the status column to exclude the new value
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'rejected', 'cancelled', 'completed')");
    }
};
