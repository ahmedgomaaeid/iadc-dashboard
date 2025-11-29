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
        Schema::table('boards', function (Blueprint $table) {
            $table->text('zoom_access_token')->nullable();
            $table->text('zoom_refresh_token')->nullable();
            $table->timestamp('zoom_token_expires_at')->nullable();
        });

        Schema::table('highboards', function (Blueprint $table) {
            $table->text('zoom_access_token')->nullable();
            $table->text('zoom_refresh_token')->nullable();
            $table->timestamp('zoom_token_expires_at')->nullable();
        });

        Schema::table('meeting_sessions', function (Blueprint $table) {
            $table->string('zoom_meeting_id')->nullable();
            $table->text('zoom_join_url')->nullable();
            $table->text('zoom_start_url')->nullable();
            $table->string('zoom_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn(['zoom_access_token', 'zoom_refresh_token', 'zoom_token_expires_at']);
        });

        Schema::table('highboards', function (Blueprint $table) {
            $table->dropColumn(['zoom_access_token', 'zoom_refresh_token', 'zoom_token_expires_at']);
        });

        Schema::table('meeting_sessions', function (Blueprint $table) {
            $table->dropColumn(['zoom_meeting_id', 'zoom_join_url', 'zoom_start_url', 'zoom_password']);
        });
    }
};
