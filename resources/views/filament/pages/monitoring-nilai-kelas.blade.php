<x-filament-panels::page>

<style>
/* ── Sticky columns: menarget <td> langsung via nth-child ───────── */

/* Header (th) — sudah sticky via extraHeaderAttributes, perkuat bg */
#mon-score-table table thead tr th:nth-child(1),
#mon-score-table table thead tr th:nth-child(2) {
    position: sticky;
    z-index: 6;
    background-color: #f9fafb;
}
.dark #mon-score-table table thead tr th:nth-child(1),
.dark #mon-score-table table thead tr th:nth-child(2) {
    background-color: #1f2937;
}

/* Body (td) — sticky harus di <td> bukan inner div */
#mon-score-table table tbody tr td:nth-child(1),
#mon-score-table table tbody tr td:nth-child(2) {
    position: sticky;
    z-index: 4;
    background-color: #ffffff;
}
.dark #mon-score-table table tbody tr td:nth-child(1),
.dark #mon-score-table table tbody tr td:nth-child(2) {
    background-color: #111827;
}
/* Hover state */
#mon-score-table table tbody tr:hover td:nth-child(1),
#mon-score-table table tbody tr:hover td:nth-child(2) {
    background-color: #f9fafb;
}
.dark #mon-score-table table tbody tr:hover td:nth-child(1),
.dark #mon-score-table table tbody tr:hover td:nth-child(2) {
    background-color: rgb(255 255 255 / 0.05);
}

/* Posisi horizontal */
#mon-score-table table thead tr th:nth-child(1),
#mon-score-table table tbody tr td:nth-child(1) {
    left: 0;
    min-width: 110px;
}
#mon-score-table table thead tr th:nth-child(2),
#mon-score-table table tbody tr td:nth-child(2) {
    left: 110px;
    min-width: 180px;
}

/* Shadow divider setelah kolom sticky ke-2 */
#mon-score-table table thead tr th:nth-child(2)::after,
#mon-score-table table tbody tr td:nth-child(2)::after {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0;
    width: 6px;
    background: linear-gradient(to right, rgba(0,0,0,0.07), transparent);
    pointer-events: none;
}
.dark #mon-score-table table thead tr th:nth-child(2)::after,
.dark #mon-score-table table tbody tr td:nth-child(2)::after {
    background: linear-gradient(to right, rgba(0,0,0,0.25), transparent);
}
</style>

@php
    $year      = $this->getActiveYear();
    $classRoom = $this->getSelectedClassRoom();
    $isAdmin   = $this->isAdminOrKepSek();
@endphp

{{-- ── No active year ──────────────────────────────────────────── --}}
@if (!$year)
    <x-filament::section>
        <p class="text-sm text-center text-gray-500 dark:text-gray-400 py-4">
            Tidak ada tahun ajaran aktif. Hubungi Super Admin.
        </p>
    </x-filament::section>

@else

    {{-- ── Class selector (admin/kepsek always, wali kelas hanya jika belum punya kelas) ── --}}
    @if ($isAdmin)
        <x-filament::section :compact="true" class="mb-4">
            <div class="max-w-sm">
                {{ $this->form }}
            </div>
        </x-filament::section>
    @endif

    {{-- ── Admin tanpa kelas dipilih: Tampilkan Filament Table Native ── --}}
    @if ($isAdmin && !$classRoom)
        <x-filament::section heading="Ringkasan Semua Kelas — {{ $year->name }}">
            {{ $this->table }}
        </x-filament::section>

    {{-- ── Wali kelas belum di-assign ── --}}
    @elseif (!$isAdmin && !$classRoom)
        <x-filament::section>
            <p class="text-sm text-center text-gray-500 dark:text-gray-400 py-4">
                Akun Anda belum ditugaskan sebagai Wali Kelas. Hubungi Super Admin.
            </p>
        </x-filament::section>

    {{-- ── Detail nilai kelas ── --}}
    @else
        @php $stats = $this->getDetailStats(); @endphp

        {{-- Stats Cards --}}
        <div class="grid grid-cols-3 gap-4 mb-4">
            <x-filament::section>
                <div class="text-center py-2">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $classRoom->name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $year->name }}</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center py-2">
                    <p class="text-xl font-bold {{ $stats['pct'] == 100 ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}">
                        {{ $stats['pct'] }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['filled'] }} / {{ $stats['total'] }} nilai terisi</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center py-2">
                    <p class="text-xl font-bold {{ $stats['done'] === $stats['total_students'] && $stats['total_students'] > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $stats['done'] }} / {{ $stats['total_students'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">siswa nilai lengkap</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Tabel detail --}}
        <x-filament::section heading="Detail Nilai — {{ $classRoom->name }}">
            <div id="mon-score-table">
                {{ $this->table }}
            </div>
        </x-filament::section>
    @endif

@endif
</x-filament-panels::page>
