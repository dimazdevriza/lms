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
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->dropUnique('cls_sub_ay_unique');
            $table->unique(['class_id', 'subject_id', 'academic_year_id', 'teacher_id'], 'cls_sub_ay_teacher_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_subject_teacher', function (Blueprint $table) {
            $table->dropUnique('cls_sub_ay_teacher_unique');
            $table->unique(['class_id', 'subject_id', 'academic_year_id'], 'cls_sub_ay_unique');
        });
    }
};
