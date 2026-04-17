<?php

namespace App\Filament\Pages;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MonitoringNilaiKelas extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $title = 'Monitoring Nilai Kelas';
    protected static ?string $navigationLabel = 'Monitoring Nilai';
    protected static \UnitEnum|string|null $navigationGroup = 'Kegiatan Belajar';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.monitoring-nilai-kelas';

    public ?string $selected_class_room_id = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole([
            'Super Admin',
            'Wali Kelas',
            'Kepala Sekolah / Kurikulum',
        ]) ?? false;
    }

    public function mount(): void
    {
        $user = auth()->user();

        // Wali Kelas auto-select kelasnya; admin/kepsek tidak
        if ($user->hasRole('Wali Kelas') && !$user->hasAnyRole(['Super Admin', 'Kepala Sekolah / Kurikulum'])) {
            $classRoom = ClassRoom::where('wali_kelas_id', $user->id)->first();
            $this->selected_class_room_id = $classRoom ? (string) $classRoom->id : null;
        }

        $this->form->fill([
            'selected_class_room_id' => $this->selected_class_room_id,
        ]);
    }

    // Dipanggil otomatis oleh Livewire saat selected_class_room_id berubah
    public function updatedSelectedClassRoomId(): void
    {
        $this->resetTable();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('selected_class_room_id')
                ->label('Pilih Kelas')
                ->options(ClassRoom::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->placeholder('Pilih kelas untuk melihat detail nilai...')
                ->live(),
        ]);
    }

    public function getActiveYear(): ?AcademicYear
    {
        return AcademicYear::active()->first();
    }

    public function getSelectedClassRoom(): ?ClassRoom
    {
        return $this->selected_class_room_id
            ? ClassRoom::find($this->selected_class_room_id)
            : null;
    }

    public function isAdminOrKepSek(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Kepala Sekolah / Kurikulum']) ?? false;
    }

    public function isShowingDetail(): bool
    {
        return $this->selected_class_room_id !== null;
    }

    public function getClassSummaries(): \Illuminate\Support\Collection
    {
        $year = $this->getActiveYear();
        if (!$year) return collect();

        return ClassRoom::orderBy('name')->get()->map(function (ClassRoom $classRoom) use ($year) {
            $subjects  = SubjectTeacher::where('class_room_id', $classRoom->id)->where('academic_year_id', $year->id)->count();
            $students  = $classRoom->students()->count();
            $expected  = $subjects * $students;
            $submitted = Score::whereHas('student', fn($q) => $q->where('class_room_id', $classRoom->id))
                ->where('academic_year_id', $year->id)->whereNotNull('score_us')->count();
            $pct = $expected > 0 ? round(($submitted / $expected) * 100) : 0;

            return [
                'id'        => $classRoom->id,
                'name'      => $classRoom->name,
                'wali'      => $classRoom->waliKelas?->name ?? '—',
                'students'  => $students,
                'subjects'  => $subjects,
                'submitted' => $submitted,
                'expected'  => $expected,
                'pct'       => $pct,
            ];
        });
    }

    public function getDetailStats(): array
    {
        $year      = $this->getActiveYear();
        $classRoom = $this->getSelectedClassRoom();
        if (!$year || !$classRoom) return ['total' => 0, 'filled' => 0, 'pct' => 0, 'done' => 0, 'total_students' => 0];

        $subjects = Subject::whereHas('subjectTeachers', fn($q) =>
            $q->where('class_room_id', $classRoom->id)->where('academic_year_id', $year->id)
        )->count();

        $students = Student::where('class_room_id', $classRoom->id)->get();
        $total    = $students->count() * $subjects;

        $scores   = Score::where('academic_year_id', $year->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()->groupBy('student_id');

        $filled = Score::where('academic_year_id', $year->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->whereNotNull('score_us')->count();

        $done = $students->filter(function ($student) use ($scores, $subjects) {
            return $scores->get($student->id, collect())->whereNotNull('score_us')->count() === $subjects;
        })->count();

        return [
            'total'         => $total,
            'filled'        => $filled,
            'pct'           => $total > 0 ? round(($filled / $total) * 100) : 0,
            'done'          => $done,
            'total_students'=> $students->count(),
        ];
    }

    // ─── Single table — adapts based on context ────────────────────────────────

    public function table(Table $table): Table
    {
        $year      = $this->getActiveYear();
        $classRoom = $this->getSelectedClassRoom();

        // ── Mode 2: Detail kelas ─────────────────────────────────────────────
        if ($year && $classRoom) {
            $subjects = Subject::whereHas('subjectTeachers', function ($q) use ($classRoom, $year) {
                $q->where('class_room_id', $classRoom->id)->where('academic_year_id', $year->id);
            })->orderBy('name')->get();

            $scores = Score::where('academic_year_id', $year->id)
                ->whereIn('student_id', Student::where('class_room_id', $classRoom->id)->pluck('id'))
                ->get()->groupBy('student_id');

            $columns = [
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->copyable()
                    ->extraHeaderAttributes(['class' => 'fi-sticky-col fi-sticky-col-1'])
                    ->extraAttributes(['class' => 'fi-sticky-col fi-sticky-col-1']),
                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->extraHeaderAttributes(['class' => 'fi-sticky-col fi-sticky-col-2'])
                    ->extraAttributes(['class' => 'fi-sticky-col fi-sticky-col-2']),
                TextColumn::make('agama')
                    ->label('Agama')
                    ->badge()
                    ->getStateUsing(fn(Student $r) => is_object($r->agama) ? $r->agama->value : ($r->agama ?? '—'))
                    ->color(fn(Student $r) => match(is_object($r->agama) ? $r->agama->value : $r->agama) {
                        'Islam'    => 'success',
                        'Kristen'  => 'info',
                        'Katolik'  => 'primary',
                        'Hindu'    => 'warning',
                        'Budha'    => 'danger',
                        'Konghucu' => 'gray',
                        default    => 'gray',
                    }),
            ];

            foreach ($subjects as $subject) {
                $sid = $subject->id;
                $columns[] = TextColumn::make('score_' . $sid)
                    ->label($subject->name)
                    ->getStateUsing(function (Student $record) use ($sid, $scores) {
                        return $scores->get($record->id, collect())->firstWhere('subject_id', $sid)?->score_us;
                    })
                    ->placeholder('—')
                    ->badge()
                    ->color(function (Student $record) use ($sid, $scores) {
                        $score = $scores->get($record->id, collect())->firstWhere('subject_id', $sid);
                        if (!$score || $score->score_us === null) return 'gray';
                        return $score->score_us >= 75 ? 'success' : 'danger';
                    })
                    ->alignCenter();
            }

            $total = $subjects->count();
            $columns[] = TextColumn::make('status')
                ->label('Status')
                ->getStateUsing(fn(Student $r) => $total === 0 ? '—'
                    : ($scores->get($r->id, collect())->whereNotNull('score_us')->count() === $total
                        ? 'Lengkap'
                        : $scores->get($r->id, collect())->whereNotNull('score_us')->count() . '/' . $total))
                ->badge()
                ->color(fn(Student $r) => $total > 0 && $scores->get($r->id, collect())->whereNotNull('score_us')->count() === $total
                    ? 'success' : 'warning')
                ->alignCenter();

            return $table
                ->query(Student::query()->where('class_room_id', $classRoom->id)->orderBy('name'))
                ->columns($columns)
                ->paginated(false);
        }

        // ── Mode 1: Ringkasan semua kelas (fallback / admin tanpa pilihan) ────
        return $table
            ->query(Student::query()->whereNull('id')) // empty, summary pakai blade
            ->columns([])
            ->paginated(false);
    }
}
