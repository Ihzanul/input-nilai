<?php

namespace App\Filament\Widgets;

use App\Models\AcademicYear;
use App\Models\Score;
use App\Models\Student;
use App\Models\SubjectTeacher;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ScoreProgressWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeYear = AcademicYear::active()->first();
        if (!$activeYear) {
            return [
                Stat::make('Status', 'Tidak ada Tahun Ajaran Aktif')
            ];
        }

        $user = auth()->user();
        if ($user->hasRole('Super Admin') || $user->hasRole('Kepala Sekolah / Kurikulum')) {
            $totalSubmitted = Score::where('academic_year_id', $activeYear->id)->whereNotNull('score_us')->count();
            return [
                Stat::make('Total Nilai Masuk', $totalSubmitted)
                    ->description('Untuk tahun ajaran aktif')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success'),
            ];
        }

        if ($user->hasRole('Guru Mata Pelajaran')) {
            $subjectTeachers = SubjectTeacher::where('teacher_id', $user->id)
                ->where('academic_year_id', $activeYear->id)
                ->get();

            // Ambil semua siswa dari kelas-kelas yang diajarkan dalam 1 query (mencegah N+1)
            $classIds = $subjectTeachers->pluck('class_room_id')->unique();
            $studentsByClass = Student::whereIn('class_room_id', $classIds)->get();

            $expectedScores = 0;
            foreach ($subjectTeachers as $st) {
                // Hitung dari collection di memori (tidak hit SQL Server)
                $students = $studentsByClass->where('class_room_id', $st->class_room_id);
                if ($st->agama_filter) {
                    // Enum comparison if applicable, but comparing values usually string.
                    $filter = is_object($st->agama_filter) ? $st->agama_filter->value : $st->agama_filter;
                    $students = $students->filter(function($s) use ($filter) {
                        return (is_object($s->agama) ? $s->agama->value : $s->agama) === $filter;
                    });
                }
                $expectedScores += $students->count();
            }

            $submittedScores = Score::where('teacher_id', $user->id)
                ->where('academic_year_id', $activeYear->id)
                ->whereNotNull('score_us')
                ->count();

            $percentage = $expectedScores > 0 ? round(($submittedScores / $expectedScores) * 100) : 0;

            return [
                Stat::make('Progres Input Nilai Anda', $percentage . '%')
                    ->description($submittedScores . ' dari ' . $expectedScores . ' nilai terisi')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color($percentage == 100 ? 'success' : 'warning'),
            ];
        }

        if ($user->hasRole('Wali Kelas')) {
            $classRoom = \App\Models\ClassRoom::where('wali_kelas_id', $user->id)->first();
            if ($classRoom) {
                $subjectsInClass = SubjectTeacher::where('class_room_id', $classRoom->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->count();
                $studentsInClass = Student::where('class_room_id', $classRoom->id)->count();
                $expected = $subjectsInClass * $studentsInClass;

                $submitted = Score::whereHas('student', function ($query) use ($classRoom) {
                        $query->where('class_room_id', $classRoom->id);
                    })
                    ->where('academic_year_id', $activeYear->id)
                    ->whereNotNull('score_us')
                    ->count();

                $percentage = $expected > 0 ? round(($submitted / $expected) * 100) : 0;

                return [
                    Stat::make('Keterisian Nilai Kelas ' . $classRoom->name, $percentage . '%')
                        ->description($submitted . ' dari ' . $expected . ' target nilai')
                        ->color($percentage == 100 ? 'success' : 'primary'),
                ];
            }
        }

        return [];
    }
}
