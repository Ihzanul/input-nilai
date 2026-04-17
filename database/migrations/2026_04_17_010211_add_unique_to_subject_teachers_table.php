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
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->unique(
                ['subject_id', 'teacher_id', 'class_room_id', 'academic_year_id'],
                'subject_teachers_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->dropUnique('subject_teachers_unique');
        });
    }
};
