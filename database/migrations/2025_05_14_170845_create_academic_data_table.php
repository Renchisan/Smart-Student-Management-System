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


        Schema::create('academic_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id'); // FK to users

            // ML-related fields
            $table->tinyInteger('studytime')->nullable();       // 1-4 (how much weekly study time)
            $table->tinyInteger('failures')->nullable();        // number of past class failures
            $table->boolean('schoolsup')->nullable();           // extra educational support (yes/no)
            $table->boolean('famsup')->nullable();              // family educational support (yes/no)
            $table->boolean('paid')->nullable();                // extra paid classes (yes/no)
            $table->boolean('activities')->nullable();          // extra-curricular activities (yes/no)
            $table->integer('absences')->nullable();            // number of school absences

            $table->float('G1')->nullable();                    // 1st period grade
            $table->float('G2')->nullable();                    // 2nd period grade

            $table->float('g1_g2_diff')->nullable();
            $table->float('g_avg')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_data');
    }
};
