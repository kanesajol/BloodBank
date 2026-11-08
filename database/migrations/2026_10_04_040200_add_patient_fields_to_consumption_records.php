<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumption_records', function (Blueprint $table) {
            $table->string('patient_name')->nullable()->after('location');
            $table->unsignedTinyInteger('age')->nullable()->after('patient_name');
            $table->string('sex', 1)->nullable()->after('age');
            $table->string('blood_component')->nullable()->after('blood_type');
            $table->string('municipality')->nullable()->after('blood_component');
            $table->string('barangay')->nullable()->after('municipality');
            $table->string('blood_type', 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('consumption_records', function (Blueprint $table) {
            $table->string('blood_type', 8)->nullable(false)->change();
            $table->dropColumn(['patient_name', 'age', 'sex', 'blood_component', 'municipality', 'barangay']);
        });
    }
};
