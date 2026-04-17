<?php

namespace App\Filament\Resources\SubjectTeachers\Pages;

use App\Filament\Resources\SubjectTeachers\SubjectTeacherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjectTeacher extends EditRecord
{
    protected static string $resource = SubjectTeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
