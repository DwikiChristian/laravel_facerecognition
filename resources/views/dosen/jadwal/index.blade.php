{{-- resources/views/dosen/jadwal/index.blade.php --}}
@extends('layouts.layout-dosen')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Jadwal Mengajar Saya</h1>
            <p class="text-gray-600">Kelola waktu mengajar Anda dengan baik</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Jadwal</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $jadwal->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Mata Kuliah</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $jadwal->pluck('matakuliah.nama')->unique()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Kelas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $jadwal->pluck('kelas.nama')->unique()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Mengajar Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $jadwal->where('hari', $currentDay ?? 'Rabu')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug info (remove in production) -->
        @if(config('app.debug'))
            <div class="mb-4 p-4 bg-yellow-100 rounded-lg">
                <p><strong>Debug Info:</strong></p>
                <p>Current Day from Controller: {{ $currentDay ?? 'Not set' }}</p>
                <p>Current Date: {{ now()->format('Y-m-d H:i:s') }}</p>
                <p>Current Day (English): {{ now()->format('l') }}</p>
            </div>
        @endif

        <!-- Quick Filter -->
        <div class="mb-6">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex flex-wrap gap-2">
                    <button class="px-4 py-2 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                        Semua Hari
                    </button>
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day)
                        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors filter-day" data-day="{{ $day }}">
                            {{ $day }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Schedule Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($jadwal as $item)
                @php
                    // Use the current day passed from controller
                    $today = $currentDay ?? 'Rabu'; // Fallback to Wednesday if not set
                    $isToday = $item->hari == $today;
                    $dayColors = [
                        'Senin' => 'from-red-400 to-red-600',
                        'Selasa' => 'from-orange-400 to-orange-600', 
                        'Rabu' => 'from-yellow-400 to-yellow-600',
                        'Kamis' => 'from-green-400 to-green-600',
                        'Jumat' => 'from-blue-400 to-blue-600',
                        'Sabtu' => 'from-indigo-400 to-indigo-600',
                        'Minggu' => 'from-purple-400 to-purple-600'
                    ];
                    $gradientClass = $dayColors[$item->hari] ?? 'from-gray-400 to-gray-600';
                @endphp

                <div class="group relative schedule-card" data-day="{{ $item->hari }}">
                    <!-- Today indicator -->
                    @if($isToday)
                        <div class="absolute -top-2 -right-2 z-10">
                            <div class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold animate-pulse">
                                HARI INI
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-2xl {{ $isToday ? 'ring-2 ring-red-300' : '' }}">
                        <!-- Header with gradient -->
                        <div class="bg-gradient-to-r {{ $gradientClass }} p-6 text-white relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
                            <div class="absolute bottom-0 left-0 w-20 h-20 bg-white bg-opacity-10 rounded-full translate-y-10 -translate-x-10"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-bold leading-tight">{{ $item->hari }}</h3>
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl px-3 py-1">
                                        <span class="text-sm font-semibold">{{ $item->matakuliah ? $item->matakuliah->kode : '-' }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-lg font-bold">
                                        {{ date('H:i', strtotime($item->jam_mulai)) }} - {{ date('H:i', strtotime($item->jam_selesai)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <div class="mb-4">
                                <h4 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-green-600 transition-colors">
                                    {{ $item->matakuliah ? $item->matakuliah->nama : 'Mata Kuliah tidak ditemukan' }}
                                </h4>
                            </div>

                            <!-- Kelas info -->
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-300 to-green-400 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 font-medium">Kelas</p>
                                    <p class="font-semibold text-gray-800">{{ $item->kelas ? $item->kelas->nama : 'Kelas tidak ditemukan' }}</p>
                                </div>
                            </div>

                            <!-- Duration and Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">
                                        @php
                                            $start = \Carbon\Carbon::parse($item->jam_mulai);
                                            $end = \Carbon\Carbon::parse($item->jam_selesai);
                                            $duration = $end->diffInMinutes($start);
                                            $hours = intval($duration / 60);
                                            $minutes = $duration % 60;
                                        @endphp
                                        {{ $hours }}j {{ $minutes }}m
                                    </span>
                                </div>
                                
                                @if($isToday)
                                    <div class="flex items-center space-x-1 text-red-600">
                                        <div class="w-2 h-2 bg-red-600 rounded-full animate-ping"></div>
                                        <span class="text-xs font-bold">MENGAJAR</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-3xl shadow-xl p-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Belum Ada Jadwal Mengajar</h3>
                        <p class="text-gray-600 mb-6">Jadwal mengajar Anda belum tersedia. Silakan hubungi admin untuk informasi lebih lanjut.</p>
                        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl font-semibold transition-colors">
                            Hubungi Admin
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Quick Actions -->
        @if($jadwal->count() > 0)
            <div class="mt-12 bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Aksi Cepat</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <button class="flex items-center space-x-4 p-4 rounded-2xl bg-green-50 hover:bg-green-100 transition-colors group">
                        <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">Lihat Kalender</p>
                            <p class="text-sm text-gray-600">Tampilan kalender lengkap</p>
                        </div>
                    </button>

                    <button class="flex items-center space-x-4 p-4 rounded-2xl bg-blue-50 hover:bg-blue-100 transition-colors group">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">Presensi</p>
                            <p class="text-sm text-gray-600">Kelola presensi mahasiswa</p>
                        </div>
                    </button>

                    <button class="flex items-center space-x-4 p-4 rounded-2xl bg-purple-50 hover:bg-purple-100 transition-colors group">
                        <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">Set Reminder</p>
                            <p class="text-sm text-gray-600">Atur pengingat mengajar</p>
                        </div>
                    </button>

                    <button class="flex items-center space-x-4 p-4 rounded-2xl bg-orange-50 hover:bg-orange-100 transition-colors group">
                        <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">Export PDF</p>
                            <p class="text-sm text-gray-600">Unduh jadwal sebagai PDF</p>
                        </div>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Add some interactive animations and filtering
document.addEventListener('DOMContentLoaded', function() {
    // Animate cards on scroll
    const cards = document.querySelectorAll('.group');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Day filter functionality
    const filterButtons = document.querySelectorAll('.filter-day');
    const scheduleCards = document.querySelectorAll('.schedule-card');
    const allButton = document.querySelector('button:first-child');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const selectedDay = this.getAttribute('data-day');
            
            // Update button styles
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-green-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            allButton.classList.remove('bg-green-600', 'text-white');
            allButton.classList.add('bg-gray-100', 'text-gray-700');
            
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('bg-green-600', 'text-white');
            
            // Filter cards
            scheduleCards.forEach(card => {
                const cardDay = card.getAttribute('data-day');
                if (cardDay === selectedDay) {
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Show all button functionality
    allButton.addEventListener('click', function() {
        // Update button styles
        filterButtons.forEach(btn => {
            btn.classList.remove('bg-green-600', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        this.classList.remove('bg-gray-100', 'text-gray-700');
        this.classList.add('bg-green-600', 'text-white');
        
        // Show all cards
        scheduleCards.forEach(card => {
            card.style.display = 'block';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endpush
@endsection