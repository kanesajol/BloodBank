<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumption_records', function (Blueprint $table) {
            $table->string('province')->nullable()->after('municipality');
        });
    }

    public function down(): void
    {
        Schema::table('consumption_records', function (Blueprint $table) {
            $table->dropColumn('province');
        });
    }
};
