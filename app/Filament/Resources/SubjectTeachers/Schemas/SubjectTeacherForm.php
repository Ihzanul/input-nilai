<?php

namespace App\Filament\Resources\SubjectTeachers\Schemas;

use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class SubjectTeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('teacher_id')
                    ->label('Guru Pengajar')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('class_room_id')
                    ->label('Kelas')
                    ->relationship('classRoom', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('agama_filter')
                    ->label('Filter Agama (Khusus Mapel Agama)')
                    ->options(\App\Enums\Agama::options())
                    ->placeholder('Semua Siswa (Tidak Difilter)')
                    ->searchable()
                    ->helperText('Kosongkan jika guru ini mengajar semua siswa. Isi jika hanya siswa beragama tertentu.'),
            ]);
    }
}
