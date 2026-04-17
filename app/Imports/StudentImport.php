<?php

namespace App\Imports;

use App\Models\ClassRoom;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentImport implements ToModel, WithHeadingRow, WithUpserts, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Resolve class name to ID if provided as string
        $classRoomId = null;
        if (!empty($row['kelas'])) {
            $classRoom = ClassRoom::where('name', $row['kelas'])->first();
            $classRoomId = $classRoom?->id;
        }

        $agamaRaw = ucfirst(strtolower(trim($row['agama'] ?? '')));

        return new Student([
            'nisn'          => $row['nisn'] ?? null,
            'name'          => $row['nama'] ?? $row['name'] ?? null,
            'agama'         => $agamaRaw ?: null,
            'class_room_id' => $classRoomId,
        ]);
    }

    public function uniqueBy()
    {
        return 'nisn';
    }
}
