<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subject extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'subjects';

    protected $fillable = ['code', 'name', 'category'];

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    /**
     * Get all scores for this subject through subject_teachers.
     * Useful for aggregate reports.
     */
    public function scoresThroughTeachers()
    {
        return $this->hasManyThrough(
            Score::class,
            SubjectTeacher::class,
            'subject_id', // FK on subject_teachers
            'subject_id', // FK on scores
            'id',         // local key on subjects
            'subject_id'  // local key on subject_teachers
        );
    }
}
