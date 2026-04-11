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
        Schema::table('dynamic_form_submissions', function (Blueprint $table) {
            $table->boolean('is_payed')->default(false);
            $table->string('accepted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_form_submissions', function (Blueprint $table) {
            $table->dropColumn(['is_payed', 'accepted_by']);
        });
    }
};
