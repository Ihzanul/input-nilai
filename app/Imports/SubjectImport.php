<?php

namespace App\Imports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;

class SubjectImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            $name = trim($row['nama_mata_pelajaran'] ?? $row['nama'] ?? '');
            $code = trim($row['kode_mapel'] ?? $row['kode'] ?? '');

            if (!$name && !$code) {
                $this->errors[] = "Baris {$rowNum}: Kolom nama dan kode keduanya kosong, baris dilewati.";
                $this->skipped++;
                continue;
            }

            Subject::updateOrCreate(
                ['code' => $code ?: null, 'name' => $name],
                [
                    'code'     => $code ?: null,
                    'name'     => $name,
                    'category' => trim($row['kategori'] ?? ''),
                ]
            );

            $this->imported++;
        }
    }
}
