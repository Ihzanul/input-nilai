<?php

namespace App\Models;

use App\Enums\Agama;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Student extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $table = 'students';

    protected $fillable = ['nisn', 'name', 'agama', 'class_room_id'];

    protected function casts(): array
    {
        return [
            'agama' => Agama::class,
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────────

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
