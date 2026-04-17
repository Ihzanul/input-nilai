<x-filament-panels::page>
    <div class="mb-4">
        {{ $this->form }}
    </div>

    @if($this->academic_year_id && $this->class_room_id && $this->subject_id)
        {{ $this->table }}
    @else
        <div class="p-6 text-center bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-gray-500 dark:text-gray-400">Silakan pilih Tahun Ajaran, Kelas, dan Mata Pelajaran untuk menampilkan daftar siswa dan menginput nilai.</p>
        </div>
    @endif
</x-filament-panels::page>
