@extends('layouts.layout-admin')

@section('content')
<div x-data="jadwalData()" x-init="fetchData(); fetchKelas(); fetchDosen(); fetchMatkul()" class="p-4">
    <div class="flex justify-between items-center mb-6 lg:mt-10">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Jadwal</h2>
        <a href="{{ route('admin.jadwal.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200">
            <i class="fas fa-plus mr-2"></i>Tambah Jadwal
        </a>
    </div>

    <!-- Loading State -->
    <template x-if="loading">
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="text-gray-500 ml-3">Memuat data...</p>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="!loading && jadwals.length === 0">
        <div class="text-center py-8">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <p class="text-gray-500 text-lg">Tidak ada data jadwal.</p>
            <p class="text-gray-400 text-sm">Klik tombol "Tambah Jadwal" untuk menambah data baru.</p>
        </div>
    </template>

    <!-- Data Table -->
    <template x-if="!loading && jadwals.length > 0">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Kuliah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(jadwal, index) in jadwals" :key="jadwal.id">
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="index + 1"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800" x-text="jadwal.hari"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="`${jadwal.jam_mulai} - ${jadwal.jam_selesai}`"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="jadwal.matakuliah?.nama || 'N/A'"></div>
                                    <div class="text-sm text-gray-500" x-text="jadwal.matakuliah?.kode || ''"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="jadwal.dosen?.nama || 'N/A'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="jadwal.kelas?.nama || 'N/A'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800" x-text="jadwal.kelas?.prodi?.nama || 'N/A'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button @click="openEditModal(jadwal)" class="text-yellow-600 hover:text-yellow-900 transition duration-150">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button @click="openDeleteModal(jadwal)" class="text-red-600 hover:text-red-900 transition duration-150">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>

    <!-- Edit Modal -->
    <div x-show="showEditModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeEditModal()"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-edit text-yellow-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Jadwal</h3>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                                    <select x-model="editingKelasId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                        <option value="">Pilih Kelas</option>
                                        <template x-for="kelas in kelass" :key="kelas.id">
                                            <option :value="kelas.id" x-text="`${kelas.nama} - ${kelas.prodi?.nama}`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Dosen</label>
                                    <select x-model="editingDosenId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                        <option value="">Pilih Dosen</option>
                                        <template x-for="dosen in dosens" :key="dosen.id">
                                            <option :value="dosen.id" x-text="dosen.nama"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mata Kuliah</label>
                                    <select x-model="editingMatkulId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                        <option value="">Pilih Mata Kuliah</option>
                                        <template x-for="matkul in matkuls" :key="matkul.id">
                                            <option :value="matkul.id" x-text="`${matkul.kode} - ${matkul.nama}`"></option>
                                        </template>
                                        
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Hari</label>
                                    <select x-model="editingHari" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                        <option value="">Pilih Hari</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                                    <input type="time" x-model="editingJamMulai" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai</label>
                                    <input type="time" x-model="editingJamSelesai" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="updateData()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Update
                    </button>
                    <button @click="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeDeleteModal()"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Konfirmasi Hapus</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Yakin ingin menghapus jadwal "<span x-text="selectedJadwal?.matakuliah?.nama" class="font-medium text-gray-900"></span>"?
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Hari <span x-text="selectedJadwal?.hari" class="font-medium text-gray-900"></span> 
                                    pada jam <span x-text="`${selectedJadwal?.jam_mulai} - ${selectedJadwal?.jam_selesai}`" class="font-medium text-gray-900"></span>.
                                </p>
                                <p class="text-sm text-red-500 mt-2 font-medium">
                                    Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteData()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                    <button @click="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function jadwalData() {
        return {
            jadwals: [],
            kelass: [],
            dosens: [],
            matkuls: [],
            loading: true,
            editingId: null,
            editingKelasId: '',
            editingDosenId: '',
            editingMatkulId: '',
            editingHari: '',
            editingJamMulai: '',
            editingJamSelesai: '',
            selectedJadwal: null,
            showEditModal: false,
            showDeleteModal: false,

            fetchData() {
                fetch('/api/jadwal')
                    .then(res => res.json())
                    .then(data => {
                        console.log('Jadwal data:', data); // Debug log
                        this.jadwals = data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching jadwal:', error);
                        this.loading = false;
                    });
            },

            fetchKelas() {
                fetch('/api/kelas')
                    .then(res => res.json())
                    .then(data => {
                        console.log('Kelas data:', data); // Debug log
                        this.kelass = data;
                    })
                    .catch(error => {
                        console.error('Error fetching kelas:', error);
                    });
            },

            fetchDosen() {
                fetch('/api/dosens')
                    .then(res => res.json())
                    .then(data => {
                        console.log('Dosen data:', data); // Debug log
                        this.dosens = data;
                    })
                    .catch(error => {
                        console.error('Error fetching dosen:', error);
                    });
            },

            fetchMatkul() {
                fetch('/api/matakuliah')
                    .then(res => res.json())
                    .then(data => {
                        console.log('Matkul data:', data); // Debug log
                        this.matkuls = data;
                    })
                    .catch(error => {
                        console.error('Error fetching mata kuliah:', error);
                    });
            },

            // Edit Modal Functions
            openEditModal(jadwal) {
                console.log('Opening edit modal for:', jadwal); // Debug log
                this.editingId = jadwal.id;
                this.editingKelasId = jadwal.kelas_id.toString();
                this.editingDosenId = jadwal.dosen_id.toString();
                this.editingMatkulId = jadwal.mata_kuliah_id.toString();
                this.editingHari = jadwal.hari;
                this.editingJamMulai = jadwal.jam_mulai;
                this.editingJamSelesai = jadwal.jam_selesai;
                this.showEditModal = true;
            },

            closeEditModal() {
                this.showEditModal = false;
                this.editingId = null;
                this.editingKelasId = '';
                this.editingDosenId = '';
                this.editingMatkulId = '';
                this.editingHari = '';
                this.editingJamMulai = '';
                this.editingJamSelesai = '';
            },

            updateData() {
                // Convert string values to integers for IDs
                const kelasId = parseInt(this.editingKelasId);
                const dosenId = parseInt(this.editingDosenId);
                const matkulId = parseInt(this.editingMatkulId);

                if (!kelasId) {
                    alert('Pilih kelas terlebih dahulu!');
                    return;
                }
                
                if (!dosenId) {
                    alert('Pilih dosen terlebih dahulu!');
                    return;
                }

                if (!matkulId) {
                    alert('Pilih mata kuliah terlebih dahulu!');
                    return;
                }

                if (!this.editingHari) {
                    alert('Pilih hari terlebih dahulu!');
                    return;
                }

                if (!this.editingJamMulai || !this.editingJamSelesai) {
                    alert('Jam mulai dan jam selesai harus diisi!');
                    return;
                }

                if (this.editingJamMulai >= this.editingJamSelesai) {
                    alert('Jam selesai harus lebih besar dari jam mulai!');
                    return;
                }

                // Format time to H:i format (remove seconds if present)
                const formatTime = (timeString) => {
                    if (!timeString) return '';
                    // If time includes seconds, remove them
                    return timeString.length > 5 ? timeString.substring(0, 5) : timeString;
                };

                const updateData = {
                    kelas_id: kelasId,
                    dosen_id: dosenId,
                    mata_kuliah_id: matkulId,
                    hari: this.editingHari,
                    jam_mulai: formatTime(this.editingJamMulai),
                    jam_selesai: formatTime(this.editingJamSelesai)
                };

                console.log('Updating with data:', updateData); // Debug log

                fetch(`/api/jadwal/${this.editingId}`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(updateData),
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => Promise.reject(err));
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Update response:', data); // Debug log
                    // Refresh data after update
                    this.fetchData();
                    this.closeEditModal();
                    alert('Jadwal berhasil diupdate!');
                })
                .catch(error => {
                    console.error('Error updating data:', error);
                    if (error.errors) {
                        // Display validation errors
                        const errorMessages = Object.values(error.errors).flat();
                        alert('Gagal mengupdate jadwal:\n' + errorMessages.join('\n'));
                    } else {
                        alert('Gagal mengupdate jadwal!');
                    }
                });
            },

            // Delete Modal Functions
            openDeleteModal(jadwal) {
                this.selectedJadwal = jadwal;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.selectedJadwal = null;
            },

            deleteData() {
                if (!this.selectedJadwal) return;

                fetch(`/api/jadwal/${this.selectedJadwal.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    this.jadwals = this.jadwals.filter(j => j.id !== this.selectedJadwal.id);
                    this.closeDeleteModal();
                    alert('Jadwal berhasil dihapus!');
                })
                .catch(error => {
                    console.error('Error deleting data:', error);
                    alert('Gagal menghapus jadwal!');
                });
            }
        }
    }
</script>
@endsection