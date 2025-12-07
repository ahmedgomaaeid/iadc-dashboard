<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meeting_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_session_id')->nullable()->after('id');
            $table->boolean('is_continuation')->default(false)->after('parent_session_id');
            $table->unsignedInteger('continuation_count')->default(0)->after('is_continuation');

            $table->foreign('parent_session_id')
                ->references('id')
                ->on('meeting_sessions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_sessions', function (Blueprint $table) {
            $table->dropForeign(['parent_session_id']);
            $table->dropColumn(['parent_session_id', 'is_continuation', 'continuation_count']);
        });
    }
};
