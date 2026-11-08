<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('donors', 'province')) {
            Schema::table('donors', function (Blueprint $table) {
                $table->string('province')->nullable()->after('municipality');
            });
        }

        $indexes = collect(Schema::getIndexes('donors'))->pluck('name');
        if (! $indexes->contains('donors_full_name_unique')) {
            Schema::table('donors', function (Blueprint $table) {
                $table->unique('full_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropUnique(['full_name']);
            $table->dropColumn('province');
        });
    }
};
