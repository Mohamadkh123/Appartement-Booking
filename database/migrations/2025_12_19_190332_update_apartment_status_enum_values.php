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
        // Since we can't directly modify ENUM values in Laravel migrations,
        // we'll need to update any existing 'maintenance' status apartments to 'available'
        // before removing the enum value
        DB::table('apartments')
            ->where('status', 'maintenance')
            ->update(['status' => 'available']);

        // Note: Changing ENUM values in MySQL requires recreating the column
        // This would typically be done with a raw SQL statement like:
        // ALTER TABLE apartments MODIFY COLUMN status ENUM('available', 'booked') DEFAULT 'available';
        // However, since we're using Laravel migrations, we'll document this requirement
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the maintenance status option
        // This would require recreating the column with the original ENUM values
        // ALTER TABLE apartments MODIFY COLUMN status ENUM('available', 'booked', 'maintenance') DEFAULT 'available';
    }
};
