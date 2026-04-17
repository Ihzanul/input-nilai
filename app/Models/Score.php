<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $table = 'scores';

    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'score_us',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'score_us'  => 'decimal:2',
            'is_locked' => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeFilled($query)
    {
        return $query->whereNotNull('score_us');
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }
}
