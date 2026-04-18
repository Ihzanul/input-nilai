<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Notifications\Notification;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Score;
use App\Models\SubjectTeacher;

class InputNilai extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $title = 'Input Nilai US';
    protected static ?string $navigationLabel = 'Input Nilai';
    protected static \UnitEnum|string|null $navigationGroup = 'Kegiatan Belajar';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.input-nilai';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Guru Mata Pelajaran']) ?? false;
    }

    public ?string $academic_year_id = null;
    public ?string $class_room_id = null;
    public ?string $subject_id = null;

    public function mount(): void
    {
        $activeYear = AcademicYear::active()->first();
        if ($activeYear) {
            $this->academic_year_id = (string) $activeYear->id;
            $this->form->fill([
                'academic_year_id' => $this->academic_year_id,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(AcademicYear::active()->pluck('name', 'id'))
                    ->required()
                    ->live(),
                Select::make('class_room_id')
                    ->label('Kelas')
                    ->options(function () {
                        if (auth()->user()->hasRole('Super Admin')) {
                            return ClassRoom::pluck('name', 'id');
                        }
                        return SubjectTeacher::where('teacher_id', auth()->id())
                            ->with('classRoom')
                            ->get()
                            ->pluck('classRoom.name', 'class_room_id');
                    })
                    ->required()
                    ->live(),
                Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->options(function (callable $get) {
                        $classId = $get('class_room_id');
                        if (!$classId) return [];
                        
                        if (auth()->user()->hasRole('Super Admin')) {
                            return Subject::pluck('name', 'id');
                        }
                        
                        return SubjectTeacher::where('teacher_id', auth()->id())
                            ->where('class_room_id', $classId)
                            ->with('subject')
                            ->get()
                            ->pluck('subject.name', 'subject_id');
                    })
                    ->required()
                    ->live(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        $academicYear = AcademicYear::find($this->academic_year_id);
        $isYearActive = $academicYear ? $academicYear->is_active : false;

        return $table
            ->query(function () {
                if (!$this->academic_year_id || !$this->class_room_id || !$this->subject_id) {
                    return Student::query()->whereNull('id');
                }

                // Cek apakah ada filter agama pada penugasan guru ini
                $agamaFilter = SubjectTeacher::where('teacher_id', auth()->id())
                    ->where('subject_id', $this->subject_id)
                    ->where('class_room_id', $this->class_room_id)
                    ->where('academic_year_id', $this->academic_year_id)
                    ->value('agama_filter');

                // Super Admin tidak terkena filter agama
                if (auth()->user()->hasRole('Super Admin')) {
                    $agamaFilter = null;
                }

                return Student::query()
                    ->where('class_room_id', $this->class_room_id)
                    ->when($agamaFilter, fn ($q) => $q->where('agama', $agamaFilter))
                    ->leftJoin('scores', function($join) {
                        $join->on('students.id', '=', 'scores.student_id')
                             ->where('scores.subject_id', '=', $this->subject_id)
                             ->where('scores.academic_year_id', '=', $this->academic_year_id);
                    })
                    ->select('students.*', 'scores.score_us', 'scores.is_locked');
            })
            ->columns([
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->sortable()
                    ->searchable(),
                TextInputColumn::make('score_us')
                    ->label('Nilai US')
                    ->type('number')
                    ->rules(['min:0', 'max:100'])
                    ->disabled(function ($record) use ($isYearActive) {
                        return $record->is_locked || !$isYearActive;
                    })
                    ->updateStateUsing(function ($record, $state) {
                        try {
                            Score::updateOrCreate(
                                [
                                    'student_id' => $record->id,
                                    'subject_id' => $this->subject_id,
                                    'academic_year_id' => $this->academic_year_id,
                                ],
                                [
                                    'teacher_id' => auth()->id(),
                                    'score_us' => $state !== '' && $state !== null ? $state : null,
                                ]
                            );
                            Notification::make()
                                ->title('Tersimpan')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal menyimpan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->paginated([10, 25, 50, 'all'])
            ->defaultPaginationPageOption(50);
    }
}
