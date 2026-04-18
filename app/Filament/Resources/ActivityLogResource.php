<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('log_name')
                    ->label('Log Name')
                    ->readOnly(),
                Forms\Components\TextInput::make('event')
                    ->label('Aksi (Event)')
                    ->readOnly(),
                Forms\Components\TextInput::make('subject_type')
                    ->label('Modul')
                    ->readOnly()
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-'),
                Forms\Components\TextInput::make('description')
                    ->label('Deskripsi')
                    ->readOnly()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('properties')
                    ->label('Perubahan Data (Properti)')
                    ->readOnly()
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '-')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('Log System')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->default('System'),
                Tables\Columns\TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Modul')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
