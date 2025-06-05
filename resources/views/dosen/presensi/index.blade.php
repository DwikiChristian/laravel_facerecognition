{{-- resources/views/dosen/presensi/index.blade.php --}}
@extends('layouts.layout-dosen')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6" x-data="presensiApp()">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Presensi Mahasiswa</h1>
            <p class="text-gray-600">Kelola presensi kelas Anda dengan mudah</p>
        </div>

        <!-- Current Time & Date -->
        <div class="mb-6">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Waktu Sekarang</p>
                        <p class="text-2xl font-bold text-gray-900" x-text="currentTime"></p>
                        <p class="text-gray-600" x-text="currentDate"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 font-medium">Hari</p>
                        <p class="text-xl font-bold text-blue-600">{{ $currentDay }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-6 py-4 rounded-2xl">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error') || (isset($error) && $error))
            <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-6 py-4 rounded-2xl">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('error') ?? $error }}</span>
                </div>
            </div>
        @endif

        <!-- Today's Schedule Cards -->
        @if($jadwalHariIni->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($jadwalHariIni as $jadwal)
                    <div class="group relative">
                        <!-- Status indicator -->
                        <div class="absolute -top-2 -right-2 z-10">
                            <div class="px-3 py-1 rounded-full text-xs font-bold
                                @if($jadwal->status === 'upcoming') bg-yellow-500 text-white animate-pulse
                                @elseif($jadwal->status === 'ongoing') bg-green-500 text-white animate-pulse
                                @else bg-gray-500 text-white
                                @endif">
                                {{ $jadwal->status_text }}
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-2xl
                            @if($jadwal->status === 'ongoing') ring-2 ring-green-300 @endif">
                            
                            <!-- Header with gradient -->
                            <div class="p-6 text-white relative overflow-hidden
                                @if($jadwal->status === 'upcoming') bg-gradient-to-r from-yellow-400 to-orange-500
                                @elseif($jadwal->status === 'ongoing') bg-gradient-to-r from-green-400 to-green-600
                                @else bg-gradient-to-r from-gray-400 to-gray-600
                                @endif">
                                
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -translate-y-16 translate-x-16"></div>
                                <div class="absolute bottom-0 left-0 w-20 h-20 bg-white bg-opacity-10 rounded-full translate-y-10 -translate-x-10"></div>
                                
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-xl font-bold leading-tight">{{ $jadwal->matakuliah->kode ?? '-' }}</h3>
                                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl px-3 py-1">
                                            <span class="text-sm font-semibold">{{ $jadwal->kelas->nama ?? '-' }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-lg font-bold">
                                            {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <div class="mb-4">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition-colors">
                                        {{ $jadwal->matakuliah->nama ?? 'Mata Kuliah tidak ditemukan' }}
                                    </h4>
                                </div>

                                <!-- Presensi Stats -->
                                <div class="flex items-center justify-between mb-6 p-4 bg-gray-50 rounded-2xl">
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-blue-600">{{ $jadwal->presensi_hadir ?? 0 }}</p>
                                        <p class="text-sm text-gray-600">Hadir</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-gray-600">{{ $jadwal->total_presensi ?? 0 }}</p>
                                        <p class="text-sm text-gray-600">Total</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xl font-bold text-green-600">
                                            @if($jadwal->total_presensi > 0)
                                                {{ round(($jadwal->presensi_hadir / $jadwal->total_presensi) * 100) }}%
                                            @else
                                                0%
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-600">Kehadiran</p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-3">
                                    <!-- Stats Button -->
                                    <button @click="showStats({{ $jadwal->id }})" 
                                            class="w-full flex items-center justify-center space-x-2 p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-semibold transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span>Lihat Statistik</span>
                                    </button>

                                    <!-- Table Presensi Button -->
                                    <button @click="showTablePresensi({{ $jadwal->id }})" 
                                            class="w-full flex items-center justify-center space-x-2 p-3 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl font-semibold transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9m-9 4h9m-9-8h9m-9 4h9"></path>
                                        </svg>
                                        <span>Tabel Presensi</span>
                                    </button>

                                    <!-- Start/Stop Presensi Buttons -->
                                    @if($jadwal->status === 'ongoing')
                                        <form method="POST" action="{{ route('dosen.presensi.start', $jadwal->id) }}" 
                                              x-data="{ submitting: false }" @submit="submitting = true">
                                            @csrf
                                            <button type="submit" :disabled="submitting"
                                                    class="w-full flex items-center justify-center space-x-2 p-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-2xl font-semibold transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-4-4V8a2 2 0 012-2V4a1 1 0 011-1h2a1 1 0 011 1v2a2 2 0 012 2v2M7 16V8a2 2 0 012-2h6a2 2 0 012 2v8"></path>
                                                </svg>
                                                <span x-text="submitting ? 'Memulai...' : 'Mulai Presensi'"></span>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('dosen.presensi.stop', $jadwal->id) }}" 
                                              x-data="{ submitting: false }" @submit="submitting = true">
                                            @csrf
                                            <button type="submit" :disabled="submitting"
                                                    class="w-full flex items-center justify-center space-x-2 p-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white rounded-2xl font-semibold transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                                                </svg>
                                                <span x-text="submitting ? 'Menghentikan...' : 'Hentikan Presensi'"></span>
                                            </button>
                                        </form>
                                    @elseif($jadwal->status === 'upcoming')
                                        <div class="text-center py-2">
                                            <p class="text-gray-500 text-sm">Kelas belum dimulai</p>
                                        </div>
                                    @else
                                        <div class="text-center py-2">
                                            <p class="text-gray-500 text-sm">Kelas telah selesai</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="bg-white rounded-3xl shadow-xl p-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Tidak Ada Jadwal Hari Ini</h3>
                    <p class="text-gray-600 mb-6">Anda tidak memiliki jadwal mengajar untuk hari {{ $currentDay }}.</p>
                    <a href="{{ route('dosen.presensi.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-semibold transition-colors inline-block">
                        Refresh Halaman
                    </a>
                </div>
            </div>
        @endif
        
        <!-- Table Presensi Section -->
        <div x-show="showTable" x-transition:enter="transition ease-out duration-300" 
            x-transition:enter-start="opacity-0 transform translate-y-4" 
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0" 
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="mt-8">
            
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Table Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold mb-2">Tabel Presensi Mahasiswa</h3>
                            <div x-show="selectedTableJadwal" class="text-blue-100">
                                <span x-text="selectedTableJadwal?.matakuliah?.nama"></span> - 
                                <span x-text="selectedTableJadwal?.kelas?.nama"></span>
                                <span class="ml-4">
                                    <span x-text="formatTime(selectedTableJadwal?.jam_mulai)"></span> - 
                                    <span x-text="formatTime(selectedTableJadwal?.jam_selesai)"></span>
                                </span>
                            </div>
                        </div>
                        <button @click="closeTable()" class="text-blue-100 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="tableLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">Memuat data mahasiswa...</p>
                </div>

                <!-- Table Stats -->
                <div x-show="!tableLoading && selectedTableJadwal" class="p-6 bg-gray-50 border-b">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center p-3 bg-white rounded-xl border-2 border-blue-200">
                            <p class="text-2xl font-bold text-blue-600" x-text="selectedTableJadwal?.stats?.total_mahasiswa || 0"></p>
                            <p class="text-xs text-blue-700 font-medium">Total</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border-2 border-green-200">
                            <p class="text-2xl font-bold text-green-600" x-text="selectedTableJadwal?.stats?.hadir || 0"></p>
                            <p class="text-xs text-green-700 font-medium">Hadir</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border-2 border-yellow-200">
                            <p class="text-2xl font-bold text-yellow-600" x-text="selectedTableJadwal?.stats?.terlambat || 0"></p>
                            <p class="text-xs text-yellow-700 font-medium">Terlambat</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border-2 border-red-200">
                            <p class="text-2xl font-bold text-red-600" x-text="selectedTableJadwal?.stats?.tidak_hadir || 0"></p>
                            <p class="text-xs text-red-700 font-medium">Tidak Hadir</p>
                        </div>
                        <div class="text-center p-3 bg-white rounded-xl border-2 border-gray-200">
                            <p class="text-2xl font-bold text-gray-600" x-text="selectedTableJadwal?.stats?.belum_presensi || 0"></p>
                            <p class="text-xs text-gray-700 font-medium">Belum Presensi</p>
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div x-show="!tableLoading" class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mahasiswa</th>
                                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Presensi</th>
                                <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Confidence</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(mahasiswa, index) in selectedTableJadwal?.mahasiswas || []" :key="mahasiswa.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="index + 1"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900" x-text="mahasiswa.nim"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900" x-text="mahasiswa.nama"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800': mahasiswa.status === 'hadir',
                                                'bg-yellow-100 text-yellow-800': mahasiswa.status === 'terlambat',
                                                'bg-red-100 text-red-800': mahasiswa.status === 'tidak_hadir',
                                                'bg-gray-100 text-gray-800': mahasiswa.status === 'belum_presensi'
                                            }"
                                            x-text="getStatusText(mahasiswa.status)">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        <span x-text="mahasiswa.waktu_presensi ? formatDateTime(mahasiswa.waktu_presensi) : '-'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        <span x-show="mahasiswa.confidence" 
                                            x-text="mahasiswa.confidence ? Math.round(mahasiswa.confidence * 100) + '%' : '-'"></span>
                                        <span x-show="!mahasiswa.confidence">-</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div x-show="!tableLoading && (!selectedTableJadwal?.mahasiswas || selectedTableJadwal?.mahasiswas.length === 0)" 
                    class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-2.239"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data Mahasiswa</h4>
                    <p class="text-gray-500">Belum ada mahasiswa terdaftar di kelas ini.</p>
                </div>

                <!-- Table Footer -->
                <div x-show="!tableLoading && selectedTableJadwal" class="bg-gray-50 px-6 py-4 border-t">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span>Total: </span>
                            <span class="font-semibold" x-text="selectedTableJadwal?.mahasiswas?.length || 0"></span>
                            <span> mahasiswa</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span>Tingkat Kehadiran: </span>
                            <span class="font-semibold text-green-600" x-text="selectedTableJadwal?.stats?.percentage || 0"></span>%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Modal (Simplified) -->
        <div x-show="showModal" x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
             @click.self="closeModal()">
            
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <!-- Modal Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold text-gray-800">Statistik Presensi</h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6" x-show="selectedJadwal">
                    <!-- Loading State -->
                    <div x-show="modalLoading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-4 text-gray-600">Memuat statistik...</p>
                    </div>

                    <!-- Stats Content -->
                    <div x-show="!modalLoading">
                        <!-- Class Info -->
                        <div class="bg-gradient-to-r from-blue-400 to-blue-600 text-white p-6 rounded-2xl mb-6">
                            <h4 class="text-xl font-bold mb-2" x-text="selectedJadwal?.matakuliah?.nama"></h4>
                            <div class="flex items-center space-x-4 text-blue-100">
                                <span x-text="selectedJadwal?.kelas?.nama"></span>
                                <span>•</span>
                                <span x-text="selectedJadwal?.matakuliah?.kode"></span>
                                <span>•</span>
                                <span>
                                    <span x-text="formatTime(selectedJadwal?.jam_mulai)"></span> - 
                                    <span x-text="formatTime(selectedJadwal?.jam_selesai)"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Detailed Stats -->
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <!-- Attendance Stats -->
                            <div class="space-y-4">
                                <div class="bg-green-50 p-4 rounded-xl text-center border-2 border-green-200">
                                    <p class="text-3xl font-bold text-green-600" x-text="selectedJadwal?.stats?.hadir || 0"></p>
                                    <p class="text-sm text-green-700 font-medium">Hadir</p>
                                </div>
                                <div class="bg-red-50 p-4 rounded-xl text-center border-2 border-red-200">
                                    <p class="text-3xl font-bold text-red-600" x-text="selectedJadwal?.stats?.tidak_hadir || 0"></p>
                                    <p class="text-sm text-red-700 font-medium">Tidak Hadir</p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-xl text-center border-2 border-yellow-200">
                                    <p class="text-3xl font-bold text-yellow-600" x-text="selectedJadwal?.stats?.terlambat || 0"></p>
                                    <p class="text-sm text-yellow-700 font-medium">Terlambat</p>
                                </div>
                            </div>

                            <!-- Summary Stats -->
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded-xl text-center border-2 border-blue-200">
                                    <p class="text-3xl font-bold text-blue-600" x-text="selectedJadwal?.stats?.total_mahasiswa || 0"></p>
                                    <p class="text-sm text-blue-700 font-medium">Total Mahasiswa</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-xl text-center border-2 border-purple-200">
                                    <p class="text-3xl font-bold text-purple-600" 
                                       x-text="selectedJadwal?.stats?.total_mahasiswa > 0 ? Math.round(((selectedJadwal?.stats?.hadir || 0) / selectedJadwal?.stats?.total_mahasiswa) * 100) + '%' : '0%'"></p>
                                    <p class="text-sm text-purple-700 font-medium">Persentase Hadir</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-xl text-center border-2 border-gray-200">
                                    <p class="text-3xl font-bold text-gray-600" x-text="selectedJadwal?.stats?.total_presensi || 0"></p>
                                    <p class="text-sm text-gray-700 font-medium">Total Presensi</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Tingkat Kehadiran</span>
                                <span x-text="selectedJadwal?.stats?.total_mahasiswa > 0 ? Math.round(((selectedJadwal?.stats?.hadir || 0) / selectedJadwal?.stats?.total_mahasiswa) * 100) + '%' : '0%'"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full transition-all duration-500"
                                     :style="`width: ${selectedJadwal?.stats?.total_mahasiswa > 0 ? Math.round(((selectedJadwal?.stats?.hadir || 0) / selectedJadwal?.stats?.total_mahasiswa) * 100) : 0}%`"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-gray-200 p-6">
                    <div class="flex justify-between">
                        <a :href="`{{ url('dosen/presensi/table') }}/${selectedJadwal?.id || ''}`" 
                           class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18m-9 8h9m-9 4h9m-9-8h9m-9 4h9"></path>
                            </svg>
                            <span>Lihat Tabel</span>
                        </a>
                        <button @click="closeModal()" 
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-semibold transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Replace the existing presensiApp function with the new complete version
    function presensiApp() {
        return {
            // Modal Stats properties
            showModal: false,
            modalLoading: false,
            selectedJadwal: null,
            
            // Table properties
            showTable: false,
            tableLoading: false,
            selectedTableJadwal: null,
            
            // Time properties
            currentTime: '',
            currentDate: '',

            init() {
                this.updateTime();
                setInterval(() => {
                    this.updateTime();
                }, 1000);
            },

            updateTime() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                this.currentDate = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },

            // Modal Stats Methods
            async showStats(jadwalId) {
                this.showModal = true;
                this.modalLoading = true;
                this.selectedJadwal = null;

                try {
                    const response = await fetch(`{{ url('dosen/presensi/live') }}/${jadwalId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            this.selectedJadwal = {
                                ...result.data.jadwal,
                                stats: {
                                    ...result.data.stats,
                                    total_mahasiswa: result.data.stats.total || 0
                                }
                            };
                        } else {
                            throw new Error(result.message || 'Gagal memuat statistik presensi');
                        }
                    } else {
                        throw new Error('Gagal memuat statistik presensi');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Gagal memuat statistik presensi');
                    this.closeModal();
                } finally {
                    this.modalLoading = false;
                }
            },

            closeModal() {
                this.showModal = false;
                this.selectedJadwal = null;
                this.modalLoading = false;
            },

            // Table Methods
            async showTablePresensi(jadwalId) {
                // Debug: Log the jadwalId value
                console.log('jadwalId received:', jadwalId);
                console.log('jadwalId type:', typeof jadwalId);
                
                if (this.showModal) {
                    this.closeModal();
                }

                this.showTable = true;
                this.tableLoading = true;
                this.selectedTableJadwal = null;

                try {
                    // Debug: Log the complete URL being constructed
                    const url = `{{ url('dosen/presensi/table') }}/${jadwalId}`;
                    console.log('Fetching URL:', url);
                    
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    // Debug: Log response status
                    console.log('Response status:', response.status);
                    console.log('Response ok:', response.ok);

                    if (response.ok) {
                        const result = await response.json();
                        console.log('Response data:', result); // Debug: Log the response data
                        
                        if (result.success) {
                            this.selectedTableJadwal = result.data;
                            
                            setTimeout(() => {
                                const tableElement = document.querySelector('[x-show="showTable"]');
                                if (tableElement) {
                                    tableElement.scrollIntoView({ 
                                        behavior: 'smooth', 
                                        block: 'start' 
                                    });
                                }
                            }, 300);
                        } else {
                            throw new Error(result.message || 'Gagal memuat data tabel presensi');
                        }
                    } else {
                        const errorData = await response.json();
                        console.log('Error response:', errorData); // Debug: Log error response
                        throw new Error(errorData.message || 'Gagal memuat data tabel presensi');
                    }
                } catch (error) {
                    console.error('Error in showTablePresensi:', error);
                    alert(error.message || 'Gagal memuat data tabel presensi');
                    this.closeTable();
                } finally {
                    this.tableLoading = false;
                }
            },

            debugShowTablePresensi(jadwalId) {
                console.group('=== DEBUG showTablePresensi ===');
                console.log('Original jadwalId:', jadwalId);
                console.log('jadwalId type:', typeof jadwalId);
                console.log('jadwalId string representation:', String(jadwalId));
                console.log('Is jadwalId truthy?', !!jadwalId);
                console.log('Is jadwalId a number?', !isNaN(jadwalId));
                console.groupEnd();
                
                // Call the actual method
                this.showTablePresensi(jadwalId);
            },

            closeTable() {
                this.showTable = false;
                this.selectedTableJadwal = null;
                this.tableLoading = false;
            },

            formatTime(timeString) {
                if (!timeString) return '';
                return timeString.substring(0, 5);
            },

            formatDateTime(dateTimeString) {
                if (!dateTimeString) return '';
                
                const date = new Date(dateTimeString);
                return date.toLocaleString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            },

            getStatusText(status) {
                const statusMap = {
                    'hadir': 'Hadir',
                    'terlambat': 'Terlambat', 
                    'tidak_hadir': 'Tidak Hadir',
                    'belum_presensi': 'Belum Presensi'
                };
                return statusMap[status] || 'Unknown';
            }
        }
    }
</script>
@endsection