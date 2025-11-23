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
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->change();
            $table->foreignId('highboard_id')->nullable()->after('board_id')->constrained()->onDelete('cascade');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->change();
            $table->foreignId('highboard_id')->nullable()->after('board_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable(false)->change();
            $table->dropForeign(['highboard_id']);
            $table->dropColumn('highboard_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable(false)->change();
            $table->dropForeign(['highboard_id']);
            $table->dropColumn('highboard_id');
        });
    }
};
