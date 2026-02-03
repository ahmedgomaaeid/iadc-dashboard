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
        Schema::create('user_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->nullableMorphs('evaluator'); // Changed to polymorphic to support Board/Highboard
            $table->foreignId('committee_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // 'joining_meeting', 'quiz', 'participation', 'interaction'
            $table->decimal('score', 8, 2);
            $table->decimal('max_score', 8, 2)->nullable();
            $table->nullableMorphs('related'); // polymorphic for Session, Quiz, etc.
            $table->timestamps();
        });

        Schema::create('management_evaluations', function (Blueprint $table) {
            $table->id();
            $table->morphs('user'); // Changed to polymorphic to support Board/Highboard
            $table->foreignId('committee_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // 'joining_meeting'
            $table->integer('score');
            $table->nullableMorphs('related'); // polymorphic for Session
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_evaluations');
        Schema::dropIfExists('user_evaluations');
    }
};
