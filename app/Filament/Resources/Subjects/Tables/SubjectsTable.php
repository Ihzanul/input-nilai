<?php

namespace App\Filament\Resources\Subjects\Tables;

use App\Imports\SubjectImport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Mapel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options(
                        \App\Models\Subject::query()
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->distinct()
                            ->pluck('category', 'category')
                            ->toArray()
                    ),
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
                            ->label('File Excel Mata Pelajaran')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data): void {
                        $importer = new SubjectImport();
                        Excel::import($importer, $data['file'], 'local');

                        if ($importer->imported > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title("Import Berhasil: {$importer->imported} mata pelajaran ditambahkan/diperbarui.")
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
                    ->action(fn () => Excel::download(
                        new \App\Exports\SubjectTemplateExport(),
                        'template_mata_pelajaran.xlsx'
                    )),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
