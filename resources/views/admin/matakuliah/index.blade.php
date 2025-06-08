@extends('layouts.layout-admin')

@section('content')
<div x-data="matakuliahData()" x-init="fetchData()" class="p-4">
    <div class="flex justify-between items-center mb-6 lg:mt-10">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Mata Kuliah</h2>
        <a href="{{ route('admin.matakuliah.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200">
            <i class="fas fa-plus mr-2"></i>Tambah Mata Kuliah
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
    <template x-if="!loading && matakuliahs.length === 0">
        <div class="text-center py-8">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-book"></i>
            </div>
            <p class="text-gray-500 text-lg">Tidak ada data mata kuliah.</p>
            <p class="text-gray-400 text-sm">Klik tombol "Tambah Mata Kuliah" untuk menambah data baru.</p>
        </div>
    </template>

    <!-- Data Table -->
    <template x-if="!loading && matakuliahs.length > 0">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mata Kuliah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(matakuliah, index) in matakuliahs" :key="matakuliah.id">
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="index + 1"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800" x-text="matakuliah.kode"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <i class="fas fa-book text-gray-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900" x-text="matakuliah.nama"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button @click="openEditModal(matakuliah)" class="text-yellow-600 hover:text-yellow-900 transition duration-150">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button @click="openDeleteModal(matakuliah)" class="text-red-600 hover:text-red-900 transition duration-150">
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
            <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-edit text-yellow-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Mata Kuliah</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode</label>
                                    <input type="text" x-model="editingKode" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" placeholder="Masukkan kode mata kuliah" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Mata Kuliah</label>
                                    <input type="text" x-model="editingNama" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" placeholder="Masukkan nama mata kuliah" />
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
                                    Yakin ingin menghapus mata kuliah "<span x-text="selectedMatakuliah?.nama" class="font-medium text-gray-900"></span>"?
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Kode: <span x-text="selectedMatakuliah?.kode" class="font-medium text-gray-900"></span>
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
    function matakuliahData() {
        return {
            matakuliahs: [],
            loading: true,
            editingId: null,
            editingKode: '',
            editingNama: '',
            selectedMatakuliah: null,
            showEditModal: false,
            showDeleteModal: false,

            fetchData() {
                fetch('/api/matakuliah')
                    .then(res => res.json())
                    .then(data => {
                        this.matakuliahs = data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching mata kuliah:', error);
                        this.loading = false;
                    });
            },

            // Edit Modal Functions
            openEditModal(matakuliah) {
                this.editingId = matakuliah.id;
                this.editingKode = matakuliah.kode;
                this.editingNama = matakuliah.nama;
                this.showEditModal = true;
            },

            closeEditModal() {
                this.showEditModal = false;
                this.editingId = null;
                this.editingKode = '';
                this.editingNama = '';
            },

            updateData() {
                if (!this.editingKode.trim()) {
                    alert('Kode mata kuliah harus diisi!');
                    return;
                }
                
                if (!this.editingNama.trim()) {
                    alert('Nama mata kuliah harus diisi!');
                    return;
                }

                fetch(`/api/matakuliah/${this.editingId}`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        kode: this.editingKode,
                        nama: this.editingNama
                    }),
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    // Refresh data after update
                    this.fetchData();
                    this.closeEditModal();
                    alert('Mata kuliah berhasil diupdate!');
                })
                .catch(error => {
                    console.error('Error updating data:', error);
                    alert('Gagal mengupdate mata kuliah!');
                });
            },

            // Delete Modal Functions
            openDeleteModal(matakuliah) {
                this.selectedMatakuliah = matakuliah;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.selectedMatakuliah = null;
            },

            deleteData() {
                if (!this.selectedMatakuliah) return;

                fetch(`/api/matakuliah/${this.selectedMatakuliah.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    this.matakuliahs = this.matakuliahs.filter(m => m.id !== this.selectedMatakuliah.id);
                    this.closeDeleteModal();
                    alert('Mata kuliah berhasil dihapus!');
                })
                .catch(error => {
                    console.error('Error deleting data:', error);
                    alert('Gagal menghapus mata kuliah!');
                });
            }
        }
    }
</script>
@endsection