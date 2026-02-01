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
        Schema::create('google_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('session_url');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->morphs('creator');
            $table->unsignedBigInteger('committee_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_sessions');
    }
};
