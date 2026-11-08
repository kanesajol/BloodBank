<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_shift_records', function (Blueprint $table) {
            $table->timestamp('logged_in_at')->nullable()->after('login_time');
            $table->timestamp('logged_out_at')->nullable()->after('logout_time');
            $table->timestamp('last_seen_at')->nullable()->after('logged_out_at');
        });
    }

    public function down(): void
    {
        Schema::table('staff_shift_records', function (Blueprint $table) {
            $table->dropColumn(['logged_in_at', 'logged_out_at', 'last_seen_at']);
        });
    }
};
