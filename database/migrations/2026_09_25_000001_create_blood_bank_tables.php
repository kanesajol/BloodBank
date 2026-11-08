<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('blood_type', 8);
            $table->unsignedTinyInteger('age');
            $table->string('gender', 20);
            $table->string('barangay');
            $table->string('municipality');
            $table->string('contact');
            $table->date('registered_date');
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->date('donated_on');
            $table->string('blood_type', 8);
            $table->string('location');
            $table->unsignedInteger('bags')->default(1);
            $table->timestamps();
        });

        Schema::create('consumption_records', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('blood_type', 8);
            $table->unsignedInteger('number_of_bags');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->string('reporting_period');
            $table->date('date_submitted');
            $table->timestamps();
        });

        Schema::create('staff_shift_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shift_name');
            $table->string('shift_time_range');
            $table->string('shift_type', 20);
            $table->date('login_date');
            $table->string('login_time', 20);
            $table->string('logout_time', 20)->nullable();
            $table->string('status', 20);
            $table->string('station');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_shift_records');
        Schema::dropIfExists('consumption_records');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donors');
    }
};
