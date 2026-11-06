<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('password_reset_code')->nullable()->after('is_active');
            $table->timestamp('password_reset_code_expires_at')->nullable()->after('password_reset_code');
        });

        // Backfill usernames for existing records based on email prefix
        $users = DB::table('users')->whereNull('username')->get();
        foreach ($users as $user) {
            $prefix = strtolower(explode('@', $user->email)[0] ?? 'user' . $user->id);
            $clean = preg_replace('/[^a-z0-9_]/', '', $prefix) ?: 'user' . $user->id;
            $username = $clean;
            $count = 1;
            while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $clean . $count++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username']);
            $table->dropColumn(['username', 'password_reset_code', 'password_reset_code_expires_at']);
        });
    }
};
