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
        Schema::table('lessons', function (Blueprint $table) {
            $table->longText('content')->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->longText('content')->change();
        });

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->longText('text_content')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->text('content')->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->text('content')->change();
        });

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->text('text_content')->change();
        });
    }
};
