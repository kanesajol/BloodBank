<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->date('birthdate')->nullable();
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->string('mbd_date')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status', 20)->nullable();
            $table->string('reason_for_deferral')->nullable();
            $table->string('reserved_for_px')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropColumn('birthdate');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['mbd_date', 'serial_number', 'status', 'reason_for_deferral', 'reserved_for_px']);
        });
    }
};
