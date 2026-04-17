<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubjectTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return ['kode_mapel', 'nama_mata_pelajaran', 'kategori'];
    }

    public function array(): array
    {
        return [
            ['MTK',  'Matematika',                  'Muatan Nasional'],
            ['BIN',  'Bahasa Indonesia',             'Muatan Nasional'],
            ['PAI',  'Pendidikan Agama Islam',       'Muatan Nasional'],
            ['PJOK', 'Pendidikan Jasmani',           'Muatan Nasional'],
            ['SBK',  'Seni Budaya dan Keterampilan', 'Muatan Nasional'],
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
