<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SubjectTeacherImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows)
    {
        $activeYear = AcademicYear::active()->first();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Baris 1 = heading

            // Resolve guru
            $email  = trim($row['email_guru'] ?? '');
            $name   = trim($row['nama_guru'] ?? '');
            $teacher = null;
            if ($email) {
                $teacher = User::where('email', $email)->first();
            }
            if (!$teacher && $name) {
                $teacher = User::where('name', $name)->first();
            }

            // Resolve mata pelajaran
            $mapelName = trim($row['mata_pelajaran'] ?? '');
            $mapelCode = trim($row['kode_mapel'] ?? '');
            $subject   = null;
            if ($mapelName) {
                $subject = Subject::where('name', $mapelName)->first();
            }
            if (!$subject && $mapelCode) {
                $subject = Subject::where('code', $mapelCode)->first();
            }

            // Resolve kelas
            $kelasName = trim($row['kelas'] ?? '');
            $classRoom = $kelasName ? ClassRoom::where('name', $kelasName)->first() : null;

            // Resolve tahun ajaran — prioritaskan kolom, fallback ke tahun aktif
            $tahunName   = trim($row['tahun_ajaran'] ?? '');
            $academicYear = null;
            if ($tahunName) {
                $academicYear = AcademicYear::where('name', $tahunName)->first();
            }
            if (!$academicYear) {
                $academicYear = $activeYear;
            }

            // Validasi semua wajib ada
            if (!$teacher) {
                $guruLabel = $email ?: $name;
                $this->errors[] = "Baris {$rowNum}: Guru '{$guruLabel}' tidak ditemukan.";
                $this->skipped++;
                continue;
            }
            if (!$subject) {
                $mapelLabel = $mapelName ?: $mapelCode;
                $this->errors[] = "Baris {$rowNum}: Mata pelajaran '{$mapelLabel}' tidak ditemukan.";
                $this->skipped++;
                continue;
            }
            if (!$classRoom) {
                $this->errors[] = "Baris {$rowNum}: Kelas '{$kelasName}' tidak ditemukan.";
                $this->skipped++;
                continue;
            }
            if (!$academicYear) {
                $this->errors[] = "Baris {$rowNum}: Tidak ada tahun ajaran aktif dan kolom tahun_ajaran kosong/tidak ditemukan.";
                $this->skipped++;
                continue;
            }

            $agamaFilter = trim($row['filter_agama'] ?? '') ?: null;

            SubjectTeacher::updateOrCreate(
                [
                    'teacher_id'       => $teacher->id,
                    'subject_id'       => $subject->id,
                    'class_room_id'    => $classRoom->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'agama_filter' => $agamaFilter,
                ]
            );

            $this->imported++;
        }
    }
}
