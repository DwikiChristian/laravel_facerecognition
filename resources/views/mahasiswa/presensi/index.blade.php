{{-- resources/views/mahasiswa/presensi/index.blade.php --}}
@extends('layouts.layout-mahasiswa')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Riwayat Presensi</h1>
            <p class="text-gray-600">Pantau kehadiran dan statistik presensi Anda</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Presensi -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Presensi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalPresensi }}</p>
                    </div>
                </div>
            </div>

            <!-- Hadir -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Hadir</p>
                        <p class="text-2xl font-bold text-green-600">{{ $hadirCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Telat -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Telat</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $telatCount }}</p>
                    </div>
                </div>
            </div>

            <!-- Alpha -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Alpha</p>
                        <p class="text-2xl font-bold text-red-600">{{ $alphaCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Progress Bar with Details -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Progress Kehadiran</h3>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-4 rounded-full transition-all duration-1000 ease-out" 
                     style="width: {{ $attendancePercentage }}%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mb-4">
                <span>0%</span>
                <span class="font-semibold">{{ $attendancePercentage }}% kehadiran</span>
                <span>100%</span>
            </div>
            
            <!-- Detailed breakdown -->
            <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $hadirCount }}</div>
                    <div class="text-sm text-gray-600">Hadir Tepat Waktu</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $telatCount }}</div>
                    <div class="text-sm text-gray-600">Hadir Terlambat</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $alphaCount }}</div>
                    <div class="text-sm text-gray-600">Tidak Hadir</div>
                </div>
            </div>
        </div>

        <!-- Presensi History -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-2xl font-bold text-gray-800">Riwayat Presensi</h3>
                <p class="text-gray-600 mt-1">Daftar lengkap presensi Anda</p>
            </div>

            @if($presensi->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($presensi as $item)
                        @php
                            $statusColors = [
                                'hadir' => 'bg-green-100 text-green-800 border-green-200',
                                'telat' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'alpha' => 'bg-red-100 text-red-800 border-red-200'
                            ];
                            $statusIcons = [
                                'hadir' => '✓',
                                'telat' => '⏰',
                                'alpha' => '✗'
                            ];
                            $statusText = [
                                'hadir' => 'Hadir',
                                'telat' => 'Telat',
                                'alpha' => 'Alpha'
                            ];
                        @endphp
                        
                        <div class="p-6 hover:bg-gray-50 transition-colors group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- Status Badge -->
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                            {{ $statusIcons[$item->status] ?? '?' }} {{ $statusText[$item->status] ?? ucfirst($item->status) }}
                                        </span>
                                    </div>

                                    <!-- Course Info -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $item->jadwal->matakuliah->nama ?? 'Mata Kuliah tidak ditemukan' }}
                                        </h4>
                                        <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                {{ $item->jadwal->dosen->nama ?? 'Dosen tidak ditemukan' }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $item->jadwal->hari }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ date('H:i', strtotime($item->jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($item->jadwal->jam_selesai)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timestamp and Details -->
                                <div class="text-right">
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ \Carbon\Carbon::parse($item->waktu_presensi)->format('d M Y') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($item->waktu_presensi)->format('H:i') }}
                                    </p>
                                    @if($item->confidence)
                                        <p class="text-xs text-gray-500 mt-1">
                                            Akurasi: {{ $item->confidence }}%
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Screenshot Preview (if available) -->
                            @if($item->bukti_screenshot)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Bukti screenshot tersedia</span>
                                        <button class="text-blue-600 hover:text-blue-800 font-medium" onclick="viewScreenshot('{{ Storage::url($item->bukti_screenshot) }}')">
                                            Lihat
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Belum Ada Riwayat Presensi</h3>
                    <p class="text-gray-600 mb-6">Anda belum memiliki catatan presensi. Mulai hadiri kelas untuk melihat riwayat presensi.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Screenshot Modal -->
<div id="screenshotModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Bukti Presensi</h3>
            <button onclick="closeScreenshot()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <img id="screenshotImage" src="" alt="Screenshot" class="max-w-full h-auto rounded-lg shadow-lg">
        </div>
    </div>
</div>

@push('scripts')
<script>
// Screenshot modal functions
function viewScreenshot(url) {
    document.getElementById('screenshotImage').src = url;
    document.getElementById('screenshotModal').classList.remove('hidden');
}

function closeScreenshot() {
    document.getElementById('screenshotModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('screenshotModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeScreenshot();
    }
});

// Animate progress bar on load
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.bg-gradient-to-r');
    if (progressBar) {
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.width = '{{ $attendancePercentage }}%';
        }, 500);
    }
});
</script>
@endpush
@endsection