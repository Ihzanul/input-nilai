<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table = 'class_rooms';

    protected $fillable = ['name', 'wali_kelas_id'];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function totalStudents(): int
    {
        return $this->students()->count();
    }
}
