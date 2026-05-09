<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicalRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('restrict');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->text('diagnosis');
            $table->text('treatment');
            $table->text('prescription')->nullable();
            $table->text('notes')->nullable();
            $table->enum('record_type', ['consultation', 'lab_result', 'imaging', 'surgery', 'follow_up', 'other'])->default('consultation');
            $table->dateTime('recorded_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_records');
    }
}
