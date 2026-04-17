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

    {{-- ── Admin tanpa kelas dipilih: tampilkan ringkasan via blade table ── --}}
    @if ($isAdmin && !$classRoom)
        @php $summaries = $this->getClassSummaries(); @endphp

        <x-filament::section heading="Ringkasan Semua Kelas — {{ $year->name }}">
            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Wali Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Siswa</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Mapel</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nilai Masuk</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Progres</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($summaries as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ $row['wali'] }}</td>
                                <td class="px-6 py-3 text-center text-gray-700 dark:text-gray-300">{{ $row['students'] }}</td>
                                <td class="px-6 py-3 text-center text-gray-700 dark:text-gray-300">{{ $row['subjects'] }}</td>
                                <td class="px-6 py-3 text-center text-gray-700 dark:text-gray-300">{{ $row['submitted'] }} / {{ $row['expected'] }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <div class="flex-1 min-w-[80px] bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full transition-all
                                                {{ $row['pct'] == 100 ? 'bg-emerald-500' : ($row['pct'] >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                                                style="width: {{ $row['pct'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold tabular-nums w-9 text-right
                                            {{ $row['pct'] == 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' }}">
                                            {{ $row['pct'] }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">Belum ada data kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
            <x-filament::section :compact="true">
                <div class="text-center py-1">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $classRoom->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $year->name }}</p>
                </div>
            </x-filament::section>

            <x-filament::section :compact="true">
                <div class="text-center py-1">
                    <p class="text-xl font-bold {{ $stats['pct'] == 100 ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}">
                        {{ $stats['pct'] }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $stats['filled'] }} / {{ $stats['total'] }} nilai terisi</p>
                </div>
            </x-filament::section>

            <x-filament::section :compact="true">
                <div class="text-center py-1">
                    <p class="text-xl font-bold {{ $stats['done'] === $stats['total_students'] && $stats['total_students'] > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $stats['done'] }} / {{ $stats['total_students'] }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">siswa nilai lengkap</p>
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
