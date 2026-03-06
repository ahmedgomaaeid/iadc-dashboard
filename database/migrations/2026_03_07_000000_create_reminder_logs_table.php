<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'task' or 'session'
            $table->unsignedBigInteger('related_id'); // task_id or session_id
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['type', 'related_id', 'user_id']);
            $table->index(['type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
