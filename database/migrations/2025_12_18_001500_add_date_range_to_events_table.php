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
        // Check if date_from already exists (migration was partially run)
        if (Schema::hasColumn('events', 'date_from')) {
            // Just add date_to if it doesn't exist
            if (!Schema::hasColumn('events', 'date_to')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->date('date_to')->nullable()->after('date_from');
                });
            }
        } else {
            // Rename date to date_from
            Schema::table('events', function (Blueprint $table) {
                $table->renameColumn('date', 'date_from');
            });

            // Add date_to column
            Schema::table('events', function (Blueprint $table) {
                $table->date('date_to')->nullable()->after('date_from');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'date_to')) {
                $table->dropColumn('date_to');
            }
        });

        if (Schema::hasColumn('events', 'date_from')) {
            Schema::table('events', function (Blueprint $table) {
                $table->renameColumn('date_from', 'date');
            });
        }
    }
};
