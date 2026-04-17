<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubjectTeacherTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return ['email_guru', 'nama_guru', 'mata_pelajaran', 'kode_mapel', 'kelas', 'tahun_ajaran', 'filter_agama'];
    }

    public function array(): array
    {
        return [
            ['guru@sekolah.com', 'Nama Guru', 'Pendidikan Agama Islam', 'PAI', 'X A', '2025/2026', 'Islam'],
            ['guru2@sekolah.com', 'Nama Guru 2', 'Matematika', 'MTK', 'X A', '2025/2026', ''],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1D6FA4']],
            ],
        ];
    }
}
