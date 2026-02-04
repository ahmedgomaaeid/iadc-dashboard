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
        Schema::create('instructor_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('google_session_id')->constrained('google_sessions')->onDelete('cascade');
            $table->integer('rating');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        // Also restore the management_evaluations table if it doesn't exist (fixing the breakage)
        if (!Schema::hasTable('management_evaluations')) {
            Schema::create('management_evaluations', function (Blueprint $table) {
                $table->id();
                $table->morphs('user'); // user_type, user_id
                $table->foreignId('committee_id')->nullable()->constrained()->onDelete('set null');
                $table->string('type'); // 'joining_meeting'
                $table->integer('score');
                $table->nullableMorphs('related'); // polymorphic for Session
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_evaluations');
    }
};
