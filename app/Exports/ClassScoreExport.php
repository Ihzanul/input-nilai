<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Score;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClassScoreExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $classRoomId;
    protected $academicYearId;
    protected $subjects;

    public function __construct($classRoomId)
    {
        $this->classRoomId = $classRoomId;
        $activeYear = AcademicYear::active()->first();
        $this->academicYearId = $activeYear ? $activeYear->id : null;

        $this->subjects = Subject::whereHas('subjectTeachers', function ($query) {
            $query->where('class_room_id', $this->classRoomId)
                  ->where('academic_year_id', $this->academicYearId);
        })->get();
    }

    public function headings(): array
    {
        $headings = ['No', 'NISN', 'Nama Siswa'];
        
        foreach ($this->subjects as $subject) {
            $headings[] = $subject->name;
        }

        return $headings;
    }

    public function array(): array
    {
        if (!$this->academicYearId) {
            return [];
        }

        $students = Student::where('class_room_id', $this->classRoomId)->orderBy('name')->get();
        
        $data = [];
        $no = 1;

        $scores = Score::where('academic_year_id', $this->academicYearId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->groupBy('student_id');

        foreach ($students as $student) {
            $row = [
                $no++,
                $student->nisn,
                $student->name,
            ];

            $studentScores = $scores->get($student->id, collect());

            foreach ($this->subjects as $subject) {
                $score = $studentScores->firstWhere('subject_id', $subject->id);
                $row[] = $score ? $score->score_us : '';
            }

            $data[] = $row;
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
