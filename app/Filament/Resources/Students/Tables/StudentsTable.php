<?php

namespace App\Filament\Resources\Students\Tables;

use App\Enums\Agama;
use App\Imports\StudentImport;
use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('agama')
                    ->label('Agama')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_object($state) ? $state->value : $state)
                    ->color(fn ($state): string => match (is_object($state) ? $state->value : $state) {
                        'Islam'    => 'success',
                        'Kristen'  => 'info',
                        'Katolik'  => 'primary',
                        'Hindu'    => 'warning',
                        'Budha'    => 'danger',
                        'Konghucu' => 'gray',
                        default    => 'gray',
                    }),
                TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('agama')
                    ->label('Filter Agama')
                    ->options(Agama::options()),
                SelectFilter::make('class_room_id')
                    ->label('Filter Kelas')
                    ->relationship('classRoom', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('import_excel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('file')
                            ->label('File Excel Siswa')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data): void {
                        Excel::import(new StudentImport(), $data['file'], 'local');
                    }),
                \Filament\Actions\Action::make('download_template')
                    ->label('Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        $filename = 'template_siswa.xlsx';
                        $export = new \App\Exports\StudentTemplateExport();
                        return Excel::download($export, $filename);
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
