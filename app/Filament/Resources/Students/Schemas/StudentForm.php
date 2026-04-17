<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Enums\Agama;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nisn')
                    ->label('NISN')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required(),
                Select::make('agama')
                    ->label('Agama')
                    ->options(Agama::options())
                    ->placeholder('Pilih Agama')
                    ->searchable(),
                Select::make('class_room_id')
                    ->label('Kelas')
                    ->relationship('classRoom', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }
}
