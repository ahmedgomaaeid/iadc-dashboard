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
        Schema::table('task_submissions', function (Blueprint $table) {
            $table->json('files')->nullable()->after('file');
        });

        // Migrate existing data
        \DB::statement("UPDATE task_submissions SET files = JSON_ARRAY(file) WHERE file IS NOT NULL");

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->dropColumn('file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_submissions', function (Blueprint $table) {
            $table->string('file')->nullable()->after('task_id');
        });

        // Migrate existing data back (take first file)
        \DB::statement("UPDATE task_submissions SET file = JSON_UNQUOTE(JSON_EXTRACT(files, '$[0]')) WHERE files IS NOT NULL AND JSON_LENGTH(files) > 0");

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->dropColumn('files');
        });
    }
};
