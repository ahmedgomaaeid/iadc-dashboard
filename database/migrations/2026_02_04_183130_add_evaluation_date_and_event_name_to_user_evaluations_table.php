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
        Schema::table('user_evaluations', function (Blueprint $table) {
            $table->date('evaluation_date')->nullable()->after('max_score');
            $table->string('event_name')->nullable()->after('evaluation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_evaluations', function (Blueprint $table) {
            $table->dropColumn(['evaluation_date', 'event_name']);
        });
    }
};
