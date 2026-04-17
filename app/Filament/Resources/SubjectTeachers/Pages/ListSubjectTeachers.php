<?php

namespace App\Filament\Resources\SubjectTeachers\Pages;

use App\Filament\Resources\SubjectTeachers\SubjectTeacherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjectTeachers extends ListRecords
{
    protected static string $resource = SubjectTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
