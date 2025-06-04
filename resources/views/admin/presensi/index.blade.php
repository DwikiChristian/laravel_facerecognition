@extends('layouts.layout-admin')

@section('content')
<div x-data="presensiData()" x-init="init()" class="p-4">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 lg:mt-10 space-y-4 lg:space-y-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitor Presensi</h2>
            <p class="text-gray-600 text-sm mt-1">Real-time monitoring presensi mahasiswa</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200 flex items-center">
                <i class="fas fa-sync-alt mr-2" :class="{'animate-spin': loading}"></i>
                Refresh
            </button>
            <button @click="exportData()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200 flex items-center">
                <i class="fas fa-download mr-2"></i>
                Export
            </button>
            <button @click="showStatsModal = true" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200 flex items-center">
                <i class="fas fa-chart-bar mr-2"></i>
                Statistik
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter mr-2 text-blue-600"></i>
            Filter & Pencarian
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode Tanggal</label>
                <div class="flex space-x-2">
                    <input type="date" x-model="filters.tanggal_mulai" @change="fetchData()" 
                           class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="self-center text-gray-500">s/d</span>
                    <input type="date" x-model="filters.tanggal_selesai" @change="fetchData()" 
                           class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Jurusan Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                <select x-model="filters.jurusan_id" @change="onJurusanChange()" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Jurusan</option>
                    <template x-for="jurusan in jurusans" :key="jurusan.id">
                        <option :value="jurusan.id" x-text="jurusan.nama"></option>
                    </template>
                </select>
            </div>

            <!-- Prodi Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                <select x-model="filters.prodi_id" @change="onProdiChange()" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Prodi</option>
                    <template x-for="prodi in filteredProdis" :key="prodi.id">
                        <option :value="prodi.id" x-text="prodi.nama"></option>
                    </template>
                </select>
            </div>

            <!-- Kelas Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                <select x-model="filters.kelas_id" @change="fetchData()" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kelas</option>
                    <template x-for="kelas in filteredKelas" :key="kelas.id">
                        <option :value="kelas.id" x-text="kelas.nama"></option>
                    </template>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select x-model="filters.status" @change="fetchData()" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="hadir">Hadir</option>
                    <option value="telat">Telat</option>
                    <option value="tidak_hadir">Tidak Hadir</option>
                    <option value="unknown">Unknown</option>
                </select>
            </div>

            <!-- Group By -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tampilan</label>
                <select x-model="filters.group_by" @change="fetchData()" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">List Biasa</option>
                    <option value="jurusan">Group by Jurusan</option>
                    <option value="prodi">Group by Prodi</option>
                    <option value="kelas">Group by Kelas</option>
                    <option value="mata_kuliah">Group by Mata Kuliah</option>
                </select>
            </div>
        </div>

        <!-- Summary Stats -->
        <template x-if="summary">
            <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600" x-text="summary.total"></div>
                    <div class="text-sm text-blue-700">Total</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600" x-text="summary.hadir"></div>
                    <div class="text-sm text-green-700">Hadir</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-yellow-600" x-text="summary.telat"></div>
                    <div class="text-sm text-yellow-700">Telat</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-red-600" x-text="summary.tidak_hadir"></div>
                    <div class="text-sm text-red-700">Tidak Hadir</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-gray-600" x-text="summary.unknown"></div>
                    <div class="text-sm text-gray-700">Unknown</div>
                </div>
            </div>
        </template>
    </div>

    <!-- Loading State -->
    <template x-if="loading">
        <div class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-500 ml-3 text-lg">Memuat data presensi...</p>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="!loading && (!presensiData || presensiData.length === 0)">
        <div class="text-center py-12 bg-white rounded-lg shadow-md">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-user-clock"></i>
            </div>
            <p class="text-gray-500 text-lg">Tidak ada data presensi.</p>
            <p class="text-gray-400 text-sm">Coba ubah filter atau periode tanggal.</p>
        </div>
    </template>

    <!-- Grouped Data Display -->
    <template x-if="!loading && filters.group_by && presensiData && presensiData.length > 0">
        <div class="space-y-6">
            <template x-for="group in presensiData" :key="group.group_info?.id || group.group_info?.tanggal">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Group Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold" x-text="getGroupTitle(group)"></h3>
                                <p class="text-blue-100 text-sm" x-text="getGroupSubtitle(group)"></p>
                            </div>
                            <div class="flex space-x-4 text-sm">
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full">
                                    Total: <span x-text="group.summary.total"></span>
                                </span>
                                <span class="bg-green-500 bg-opacity-80 px-3 py-1 rounded-full">
                                    Hadir: <span x-text="group.summary.hadir"></span>
                                </span>
                                <span class="bg-red-500 bg-opacity-80 px-3 py-1 rounded-full">
                                    Tidak Hadir: <span x-text="group.summary.tidak_hadir"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Group Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIM</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Kuliah</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="presensi in group.presensis" :key="presensi.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="presensi.mahasiswa.nama"></div>
                                            <div class="text-xs text-gray-500" x-text="presensi.mahasiswa.kelas.nama + ' - ' + presensi.mahasiswa.kelas.prodi.nama"></div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900" x-text="presensi.mahasiswa.nim"></td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900" x-text="presensi.jadwal.matakuliah.nama"></div>
                                            <div class="text-xs text-gray-500" x-text="presensi.jadwal.dosen.nama"></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900" x-text="formatDateTime(presensi.waktu_presensi)"></div>
                                            <div class="text-xs text-gray-500" x-text="presensi.jadwal.hari + ', ' + presensi.jadwal.jam_mulai + '-' + presensi.jadwal.jam_selesai"></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span :class="getStatusClass(presensi.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="getStatusText(presensi.status)"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <template x-if="presensi.confidence">
                                                <div class="flex items-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="h-2 rounded-full" :class="getConfidenceColor(presensi.confidence)" :style="'width: ' + presensi.confidence + '%'"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-600" x-text="Math.round(presensi.confidence) + '%'"></span>
                                                </div>
                                            </template>
                                            <template x-if="!presensi.confidence">
                                                <span class="text-xs text-gray-400">N/A</span>
                                            </template>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex space-x-2">
                                                <button @click="openStatusModal(presensi)" class="text-blue-600 hover:text-blue-900 text-sm">
                                                    <i class="fas fa-edit mr-1"></i>Edit
                                                </button>
                                                <button @click="viewDetail(presensi)" class="text-green-600 hover:text-green-900 text-sm">
                                                    <i class="fas fa-eye mr-1"></i>Detail
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Regular Table (Non-grouped) -->
    <template x-if="!loading && !filters.group_by && presensiData && presensiData.data && presensiData.data.length > 0">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIM</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Kuliah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="presensi in presensiData.data" :key="presensi.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900" x-text="presensi.mahasiswa.nama"></div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900" x-text="presensi.mahasiswa.nim"></td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900" x-text="presensi.mahasiswa.kelas.nama"></div>
                                    <div class="text-xs text-gray-500" x-text="presensi.mahasiswa.kelas.prodi.nama"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900" x-text="presensi.jadwal.matakuliah.nama"></div>
                                    <div class="text-xs text-gray-500" x-text="presensi.jadwal.dosen.nama"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900" x-text="formatDateTime(presensi.waktu_presensi)"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="getStatusClass(presensi.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="getStatusText(presensi.status)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="presensi.confidence">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="h-2 rounded-full" :class="getConfidenceColor(presensi.confidence)" :style="'width: ' + presensi.confidence + '%'"></div>
                                            </div>
                                            <span class="text-xs text-gray-600" x-text="Math.round(presensi.confidence) + '%'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!presensi.confidence">
                                        <span class="text-xs text-gray-400">N/A</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex space-x-2">
                                        <button @click="openStatusModal(presensi)" class="text-blue-600 hover:text-blue-900 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <button @click="viewDetail(presensi)" class="text-green-600 hover:text-green-900 text-sm">
                                            <i class="fas fa-eye mr-1"></i>Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <template x-if="presensiData.last_page > 1">
                <div class="bg-white px-4 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span x-text="presensiData.from"></span> - <span x-text="presensiData.to"></span> dari <span x-text="presensiData.total"></span> data
                        </div>
                        <div class="flex space-x-1">
                            <button @click="changePage(presensiData.current_page - 1)" :disabled="presensiData.current_page <= 1" 
                                    class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded disabled:opacity-50">
                                Prev
                            </button>
                            <template x-for="page in getVisiblePages()" :key="page">
                                <button @click="changePage(page)" :class="page === presensiData.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'" 
                                        class="px-3 py-1 text-sm rounded" x-text="page"></button>
                            </template>
                            <button @click="changePage(presensiData.current_page + 1)" :disabled="presensiData.current_page >= presensiData.last_page" 
                                    class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded disabled:opacity-50">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Status Edit Modal -->
    <div x-show="showStatusModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div x-show="showStatusModal" x-transition:enter="ease-out duration-300" class="fixed inset-0 bg-gray-500 opacity-75"></div>
            <div x-show="showStatusModal" x-transition:enter="ease-out duration-300" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Status Presensi</h3>
                    <template x-if="selectedPresensi">
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-3 rounded">
                                <p class="text-sm"><strong>Mahasiswa:</strong> <span x-text="selectedPresensi.mahasiswa.nama"></span></p>
                                <p class="text-sm"><strong>NIM:</strong> <span x-text="selectedPresensi.mahasiswa.nim"></span></p>
                                <p class="text-sm"><strong>Mata Kuliah:</strong> <span x-text="selectedPresensi.jadwal.matakuliah.nama"></span></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select x-model="editStatus" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="hadir">Hadir</option>
                                    <option value="telat">Telat</option>
                                    <option value="tidak_hadir">Tidak Hadir</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                <textarea x-model="editCatatan" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Alasan perubahan status..."></textarea>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="updateStatus()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button @click="closeStatusModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Modal -->
    <div x-show="showStatsModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div x-show="showStatsModal" x-transition:enter="ease-out duration-300" class="fixed inset-0 bg-gray-500 opacity-75" @click="showStatsModal = false"></div>
            <div x-show="showStatsModal" x-transition:enter="ease-out duration-300" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Statistik Presensi Detail</h3>
                    <template x-if="stats">
                        <div class="space-y-6">
                            <!-- Main Stats -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-blue-600" x-text="stats.total_presensi"></div>
                                    <div class="text-sm text-blue-700">Total Presensi</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-green-600" x-text="stats.hadir"></div>
                                    <div class="text-sm text-green-700">Hadir</div>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-yellow-600" x-text="stats.telat"></div>
                                    <div class="text-sm text-yellow-700">Telat</div>
                                </div>
                                <div class="bg-red-50 p-4 rounded-lg text-center">
                                    <div class="text-3xl font-bold text-red-600" x-text="stats.tidak_hadir"></div>
                                    <div class="text-sm text-red-700">Tidak Hadir</div>
                                </div>
                            </div>

                            <!-- Confidence Stats -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Analisis Confidence</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-purple-600" x-text="Math.round(stats.confidence_avg || 0) + '%'"></div>
                                        <div class="text-sm text-gray-600">Rata-rata Confidence</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-orange-600" x-text="stats.low_confidence || 0"></div>
                                        <div class="text-sm text-gray-600"><div class="text-sm text-gray-600">Confidence Rendah (<80%)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts Section -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- By Hour Chart -->
                                <div class="bg-white border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Presensi per Jam</h4>
                                    <template x-if="stats.by_hour && stats.by_hour.length > 0">
                                        <div class="space-y-2">
                                            <template x-for="hour in stats.by_hour" :key="hour.hour">
                                                <div class="flex items-center">
                                                    <div class="w-16 text-sm text-gray-600" x-text="hour.hour + ':00'"></div>
                                                    <div class="flex-1 bg-gray-200 rounded-full h-4 mx-2">
                                                        <div class="bg-blue-600 h-4 rounded-full" :style="'width: ' + (hour.total / Math.max(...stats.by_hour.map(h => h.total)) * 100) + '%'"></div>
                                                    </div>
                                                    <div class="w-12 text-sm text-gray-900" x-text="hour.total"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <!-- By Mata Kuliah Chart -->
                                <div class="bg-white border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Top Mata Kuliah</h4>
                                    <template x-if="stats.by_mata_kuliah && stats.by_mata_kuliah.length > 0">
                                        <div class="space-y-2">
                                            <template x-for="mk in stats.by_mata_kuliah.slice(0, 5)" :key="mk.nama">
                                                <div class="flex items-center">
                                                    <div class="w-24 text-xs text-gray-600 truncate" x-text="mk.nama"></div>
                                                    <div class="flex-1 bg-gray-200 rounded-full h-3 mx-2">
                                                        <div class="bg-green-600 h-3 rounded-full" :style="'width: ' + (mk.total / Math.max(...stats.by_mata_kuliah.map(m => m.total)) * 100) + '%'"></div>
                                                    </div>
                                                    <div class="w-10 text-sm text-gray-900" x-text="mk.total"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- By Jurusan -->
                            <div class="bg-white border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-3">Presensi per Jurusan</h4>
                                <template x-if="stats.by_jurusan && stats.by_jurusan.length > 0">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <template x-for="jurusan in stats.by_jurusan" :key="jurusan.nama">
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                                <span class="text-sm font-medium text-gray-900" x-text="jurusan.nama"></span>
                                                <span class="text-lg font-bold text-blue-600" x-text="jurusan.total"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="showStatsModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Data -->
<script>
function presensiData() {
    return {
        // Data Properties
        presensiData: null,
        summary: null,
        stats: null,
        loading: false,
        
        // Filter Properties
        filters: {
            tanggal_mulai: new Date().toISOString().split('T')[0],
            tanggal_selesai: new Date().toISOString().split('T')[0],
            jurusan_id: '',
            prodi_id: '',
            kelas_id: '',
            status: '',
            mata_kuliah_id: '',
            group_by: '',
            per_page: 50,
            search: ''
        },
        
        // Master Data
        jurusans: [],
        prodis: [],
        kelas: [],
        mataKuliahs: [],
        
        // Filtered Data
        filteredProdis: [],
        filteredKelas: [],
        
        // Modal Properties
        showStatusModal: false,
        showStatsModal: false,
        selectedPresensi: null,
        editStatus: '',
        editCatatan: '',
        
        // Pagination
        currentPage: 1,

        // Initialization
        init() {
            this.loadMasterData();
            this.fetchData();
            
            // Auto refresh every 5 minutes
            setInterval(() => {
                this.refreshData();
            }, 300000);
        },

        // Load Master Data - Fixed to use correct endpoint
        async loadMasterData() {
            try {
                const response = await fetch('/admin/presensi/master/data?all=1', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.jurusans = result.data.jurusans || [];
                    this.prodis = result.data.prodis || [];
                    this.kelas = result.data.kelas || [];
                    this.mataKuliahs = result.data.matakuliahs || [];
                    
                    this.filteredProdis = this.prodis;
                    this.filteredKelas = this.kelas;
                } else {
                    throw new Error(result.message || 'Gagal memuat data master');
                }
            } catch (error) {
                console.error('Error loading master data:', error);
                this.showNotification('Gagal memuat data master', 'error');
            }
        },

        // Fetch Presensi Data - Fixed to use correct endpoint
        async fetchData() {
            this.loading = true;
            
            try {
                const params = new URLSearchParams();
                
                // Add all filters to params
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key] !== '' && this.filters[key] !== null) {
                        params.append(key, this.filters[key]);
                    }
                });
                
                if (this.currentPage > 1) {
                    params.append('page', this.currentPage);
                }
                
                const response = await fetch(`/admin/presensi?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.presensiData = result.data;
                    this.summary = result.summary;
                } else {
                    throw new Error(result.message || 'Gagal memuat data');
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                this.showNotification('Gagal memuat data presensi', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Fetch Statistics - Fixed to use correct endpoint
        async fetchStats() {
            try {
                const params = new URLSearchParams();
                
                // Apply same filters except group_by and per_page for stats
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key] !== '' && this.filters[key] !== null && 
                        key !== 'group_by' && key !== 'per_page') {
                        params.append(key, this.filters[key]);
                    }
                });
                
                const response = await fetch(`/admin/presensi/stats?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.stats = result.data;
                } else {
                    throw new Error(result.message || 'Gagal memuat statistik');
                }
            } catch (error) {
                console.error('Error fetching stats:', error);
                this.showNotification('Gagal memuat statistik', 'error');
            }
        },

        // Filter Handlers - Load filtered data dynamically
        async onJurusanChange() {
            this.filters.prodi_id = '';
            this.filters.kelas_id = '';
            
            if (this.filters.jurusan_id) {
                // Fetch prodis for selected jurusan
                try {
                    const response = await fetch(`/admin/presensi/master/data?prodis=1&jurusan_id=${this.filters.jurusan_id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.filteredProdis = result.data.prodis || [];
                    }
                } catch (error) {
                    console.error('Error loading prodis:', error);
                    this.filteredProdis = this.prodis.filter(prodi => 
                        prodi.jurusan_id == this.filters.jurusan_id
                    );
                }
            } else {
                this.filteredProdis = this.prodis;
            }
            
            this.filteredKelas = [];
            this.fetchData();
        },

        async onProdiChange() {
            this.filters.kelas_id = '';
            
            if (this.filters.prodi_id) {
                // Fetch kelas for selected prodi
                try {
                    const response = await fetch(`/admin/presensi/master/data?kelas=1&prodi_id=${this.filters.prodi_id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.filteredKelas = result.data.kelas || [];
                    }
                } catch (error) {
                    console.error('Error loading kelas:', error);
                    this.filteredKelas = this.kelas.filter(kelas => 
                        kelas.prodi_id == this.filters.prodi_id
                    );
                }
            } else if (this.filters.jurusan_id) {
                const prodiIds = this.filteredProdis.map(prodi => prodi.id);
                this.filteredKelas = this.kelas.filter(kelas => 
                    prodiIds.includes(kelas.prodi_id)
                );
            } else {
                this.filteredKelas = this.kelas;
            }
            
            this.fetchData();
        },

        // Other filter changes
        onFilterChange() {
            this.currentPage = 1;
            this.fetchData();
        },

        // Pagination
        changePage(page) {
            this.currentPage = page;
            this.fetchData();
        },

        getVisiblePages() {
            if (!this.presensiData || !this.presensiData.last_page) return [];
            
            const current = this.presensiData.current_page || this.currentPage;
            const total = this.presensiData.last_page;
            const pages = [];
            
            const start = Math.max(1, current - 2);
            const end = Math.min(total, current + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },

        // Status Modal
        openStatusModal(presensi) {
            this.selectedPresensi = presensi;
            this.editStatus = presensi.status;
            this.editCatatan = presensi.catatan || '';
            this.showStatusModal = true;
        },

        closeStatusModal() {
            this.showStatusModal = false;
            this.selectedPresensi = null;
            this.editStatus = '';
            this.editCatatan = '';
        },

        // Update Status - Fixed to use correct endpoint and method
        async updateStatus() {
            if (!this.selectedPresensi) return;
            
            try {
                const response = await fetch(`/admin/presensi/${this.selectedPresensi.id}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: this.editStatus,
                        catatan: this.editCatatan
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showNotification('Status berhasil diperbarui', 'success');
                    this.closeStatusModal();
                    this.fetchData();
                } else {
                    throw new Error(result.message || 'Gagal memperbarui status');
                }
            } catch (error) {
                console.error('Error updating status:', error);
                this.showNotification('Gagal memperbarui status', 'error');
            }
        },

        // Stats Modal
        async openStatsModal() {
            this.showStatsModal = true;
            await this.fetchStats();
        },

        closeStatsModal() {
            this.showStatsModal = false;
            this.stats = null;
        },

        // Actions
        refreshData() {
            this.currentPage = 1;
            this.fetchData();
        },

        clearFilters() {
            this.filters = {
                tanggal_mulai: new Date().toISOString().split('T')[0],
                tanggal_selesai: new Date().toISOString().split('T')[0],
                jurusan_id: '',
                prodi_id: '',
                kelas_id: '',
                status: '',
                mata_kuliah_id: '',
                group_by: '',
                per_page: 50,
                search: ''
            };
            this.filteredProdis = this.prodis;
            this.filteredKelas = this.kelas;
            this.currentPage = 1;
            this.fetchData();
        },

        // View Detail - Fixed to use correct URL
        viewDetail(presensi) {
            window.open(`/admin/presensi/${presensi.id}`, '_blank');
        },

        // Export function (placeholder - implement based on your needs)
        async exportData() {
            try {
                const params = new URLSearchParams();
                
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key] !== '' && this.filters[key] !== null && 
                        key !== 'group_by' && key !== 'per_page') {
                        params.append(key, this.filters[key]);
                    }
                });
                
                // Create export URL - you'll need to implement this endpoint
                const exportUrl = `/admin/presensi/export?${params.toString()}`;
                window.open(exportUrl, '_blank');
                
                this.showNotification('Export dimulai...', 'success');
            } catch (error) {
                console.error('Error exporting data:', error);
                this.showNotification('Gagal export data', 'error');
            }
        },

        // Utility Functions
        formatDateTime(datetime) {
            if (!datetime) return '-';
            return new Date(datetime).toLocaleString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },

        getStatusText(status) {
            const statusMap = {
                'hadir': 'Hadir',
                'telat': 'Telat',
                'tidak_hadir': 'Tidak Hadir',
                'unknown': 'Unknown'
            };
            return statusMap[status] || status;
        },

        getStatusClass(status) {
            const statusClasses = {
                'hadir': 'bg-green-100 text-green-800',
                'telat': 'bg-yellow-100 text-yellow-800',
                'tidak_hadir': 'bg-red-100 text-red-800',
                'unknown': 'bg-gray-100 text-gray-800'
            };
            return statusClasses[status] || 'bg-gray-100 text-gray-800';
        },

        getConfidenceColor(confidence) {
            if (!confidence) return 'bg-gray-500';
            if (confidence >= 90) return 'bg-green-500';
            if (confidence >= 80) return 'bg-yellow-500';
            if (confidence >= 70) return 'bg-orange-500';
            return 'bg-red-500';
        },

        getConfidenceText(confidence) {
            if (!confidence) return 'N/A';
            return Math.round(confidence) + '%';
        },

        getGroupTitle(group) {
            const groupInfo = group.group_info;
            if (groupInfo.tanggal) return groupInfo.tanggal;
            return groupInfo.nama || 'Unknown';
        },

        getGroupSubtitle(group) {
            return `${group.summary.total} presensi`;
        },

        // Notification system
        showNotification(message, type = 'info') {
            // Simple implementation - you can replace with toast library
            const notificationTypes = {
                'success': '✅',
                'error': '❌',
                'warning': '⚠️',
                'info': 'ℹ️'
            };
            
            const icon = notificationTypes[type] || notificationTypes.info;
            
            if (type === 'error') {
                console.error(message);
                alert(`${icon} Error: ${message}`);
            } else {
                console.log(message);
                alert(`${icon} ${message}`);
            }
        },

        // Helper for checking if data is grouped
        isGroupedData() {
            return this.filters.group_by !== '';
        },

        // Helper for pagination info
        getPaginationInfo() {
            if (!this.presensiData || this.isGroupedData()) return '';
            
            const { current_page, per_page, total, from, to } = this.presensiData;
            return `Menampilkan ${from || 0} sampai ${to || 0} dari ${total || 0} data`;
        }
    }
}
</script>

@endsection