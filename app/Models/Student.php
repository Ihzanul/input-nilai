<?php

namespace App\Models;

use App\Enums\Agama;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
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
