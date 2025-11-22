<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('committee_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing data
        $users = DB::table('users')->whereNotNull('committee_id')->get();
        foreach ($users as $user) {
            DB::table('committee_user')->insert([
                'user_id' => $user->id,
                'committee_id' => $user->committee_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the old column
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['committee_id']);
            $table->dropColumn('committee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('committee_id')->nullable()->constrained()->cascadeOnDelete();
        });

        // Restore data (this is lossy if a user has multiple committees, we just take the first one)
        $pivotData = DB::table('committee_user')->get();
        foreach ($pivotData as $data) {
            DB::table('users')->where('id', $data->user_id)->update(['committee_id' => $data->committee_id]);
        }

        Schema::dropIfExists('committee_user');
    }
};
