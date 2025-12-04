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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('visibility', ['global', 'private'])->default('global')->after('is_active');
            $table->unsignedBigInteger('committee_id')->nullable()->after('visibility');
            $table->foreign('committee_id')->references('id')->on('committees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['committee_id']);
            $table->dropColumn(['visibility', 'committee_id']);
        });
    }
};
