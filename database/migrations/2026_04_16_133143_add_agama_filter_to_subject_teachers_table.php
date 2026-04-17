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
            $table->string('agama_filter')->nullable()->after('academic_year_id')->comment('Jika diisi, hanya siswa beragama ini yang tampil saat input nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->dropColumn('agama_filter');
        });
    }
};
