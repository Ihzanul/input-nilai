<?php

namespace App\Filament\Resources\SubjectTeachers\Tables;

use App\Imports\SubjectTeacherImport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class SubjectTeachersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('academicYear.name')
                    ->label('Tahun Ajaran')
                    ->sortable(),
                TextColumn::make('agama_filter')
                    ->label('Filter Agama')
                    ->badge()
                    ->placeholder('Semua Siswa')
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
                            ->label('File Excel Guru Pengampu')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data): void {
                        $importer = new SubjectTeacherImport();
                        Excel::import($importer, $data['file'], 'local');

                        if ($importer->imported > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title("Import Berhasil: {$importer->imported} data guru pengampu ditambahkan/diperbarui.")
                                ->success()
                                ->send();
                        }

                        if ($importer->skipped > 0) {
                            $errorList = implode("\n", $importer->errors);
                            \Filament\Notifications\Notification::make()
                                ->title("{$importer->skipped} baris dilewati")
                                ->body($errorList)
                                ->warning()
                                ->persistent()
                                ->send();
                        }

                        if ($importer->imported === 0 && $importer->skipped === 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('File kosong atau tidak ada data yang dapat diproses.')
                                ->warning()
                                ->send();
                        }
                    }),
                \Filament\Actions\Action::make('download_template')
                    ->label('Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        $export = new \App\Exports\SubjectTeacherTemplateExport();
                        return Excel::download($export, 'template_guru_pengampu.xlsx');
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
