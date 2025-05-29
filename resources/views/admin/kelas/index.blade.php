@extends('layouts.layout-admin')

@section('content')
<div x-data="kelasData()" x-init="fetchData(); fetchProdis()" class="p-4">
    <div class="flex justify-between items-center mb-6 lg:mt-10">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Kelas</h2>
        <button @click="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition duration-200">
            <i class="fas fa-plus mr-2"></i>Tambah Kelas
        </button>
    </div>

    <!-- Loading State -->
    <template x-if="loading">
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="text-gray-500 ml-3">Memuat data...</p>
        </div>
    </template>

    <!-- Empty State -->
    <template x-if="!loading && kelass.length === 0">
        <div class="text-center py-8">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <p class="text-gray-500 text-lg">Tidak ada data kelas.</p>
            <p class="text-gray-400 text-sm">Klik tombol "Tambah Kelas" untuk menambah data baru.</p>
        </div>
    </template>

    <!-- Data Table -->
    <template x-if="!loading && kelass.length > 0">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program Studi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurusan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Mahasiswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(kelas, index) in kelass" :key="kelas.id">
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="index + 1"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="kelas.nama"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" x-text="kelas.prodi?.nama || 'N/A'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="kelas.prodi?.jurusan?.nama || 'N/A'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800" x-text="kelas.mahasiswa?.length || 0"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button @click="openEditModal(kelas)" class="text-yellow-600 hover:text-yellow-900 transition duration-150">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button @click="openDeleteModal(kelas)" class="text-red-600 hover:text-red-900 transition duration-150">
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

    <!-- Add Modal -->
    <div x-show="showAddModal" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeAddModal()"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Tambah Kelas Baru</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                                    <select x-model="newProdiId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Program Studi</option>
                                        <template x-for="prodi in prodis" :key="prodi.id">
                                            <option :value="prodi.id" x-text="`${prodi.nama} - ${prodi.jurusan?.nama}`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                                    <input type="text" x-model="newNama" placeholder="Masukkan nama kelas (contoh: A, B, 1A, 2B)" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="storeData()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button @click="closeAddModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Kelas</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                                    <select x-model="editingProdiId" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                                        <option value="">Pilih Program Studi</option>
                                        <template x-for="prodi in prodis" :key="prodi.id">
                                            <option :value="prodi.id" x-text="`${prodi.nama} - ${prodi.jurusan?.nama}`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                                    <input type="text" x-model="editingNama" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" />
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
                                    Yakin ingin menghapus kelas "<span x-text="selectedKelas?.nama" class="font-medium text-gray-900"></span>"?
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Kelas dari program studi "<span x-text="selectedKelas?.prodi?.nama" class="font-medium text-gray-900"></span>".
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
    function kelasData() {
        return {
            kelass: [],
            prodis: [],
            loading: true,
            newNama: '',
            newProdiId: '',
            editingId: null,
            editingNama: '',
            editingProdiId: '',
            selectedKelas: null,
            showAddModal: false,
            showEditModal: false,
            showDeleteModal: false,

            fetchData() {
                fetch('/api/kelas')
                    .then(res => res.json())
                    .then(data => {
                        this.kelass = data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching kelas:', error);
                        this.loading = false;
                    });
            },

            fetchProdis() {
                fetch('/api/prodis')
                    .then(res => res.json())
                    .then(data => {
                        this.prodis = data;
                    })
                    .catch(error => {
                        console.error('Error fetching prodis:', error);
                    });
            },

            // Add Modal Functions
            openAddModal() {
                this.newNama = '';
                this.newProdiId = '';
                this.showAddModal = true;
            },

            closeAddModal() {
                this.showAddModal = false;
                this.newNama = '';
                this.newProdiId = '';
            },

            storeData() {
                if (!this.newNama.trim()) {
                    alert('Nama kelas tidak boleh kosong!');
                    return;
                }
                
                if (!this.newProdiId) {
                    alert('Pilih program studi terlebih dahulu!');
                    return;
                }

                fetch('/api/kelas', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        nama: this.newNama,
                        prodi_id: this.newProdiId
                    }),
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    // Find the prodi data to add to the new kelas
                    const prodi = this.prodis.find(p => p.id == this.newProdiId);
                    data.prodi = prodi;
                    
                    this.kelass.push(data);
                    this.closeAddModal();
                    alert('Kelas berhasil ditambahkan!');
                })
                .catch(error => {
                    console.error('Error storing data:', error);
                    alert('Gagal menambahkan kelas!');
                });
            },

            // Edit Modal Functions
            openEditModal(kelas) {
                this.editingId = kelas.id;
                this.editingNama = kelas.nama;
                this.editingProdiId = kelas.prodi_id;
                this.showEditModal = true;
            },

            closeEditModal() {
                this.showEditModal = false;
                this.editingId = null;
                this.editingNama = '';
                this.editingProdiId = '';
            },

            updateData() {
                if (!this.editingNama.trim()) {
                    alert('Nama kelas tidak boleh kosong!');
                    return;
                }
                
                if (!this.editingProdiId) {
                    alert('Pilih program studi terlebih dahulu!');
                    return;
                }

                fetch(`/api/kelas/${this.editingId}`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ 
                        nama: this.editingNama,
                        prodi_id: this.editingProdiId
                    }),
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(updated => {
                    const index = this.kelass.findIndex(k => k.id === this.editingId);
                    if (index !== -1) {
                        // Find the prodi data to add to the updated kelas
                        const prodi = this.prodis.find(p => p.id == this.editingProdiId);
                        updated.prodi = prodi;
                        
                        this.kelass[index] = updated;
                    }
                    this.closeEditModal();
                    alert('Kelas berhasil diupdate!');
                })
                .catch(error => {
                    console.error('Error updating data:', error);
                    alert('Gagal mengupdate kelas!');
                });
            },

            // Delete Modal Functions
            openDeleteModal(kelas) {
                this.selectedKelas = kelas;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.selectedKelas = null;
            },

            deleteData() {
                if (!this.selectedKelas) return;

                fetch(`/api/kelas/${this.selectedKelas.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    this.kelass = this.kelass.filter(k => k.id !== this.selectedKelas.id);
                    this.closeDeleteModal();
                    alert('Kelas berhasil dihapus!');
                })
                .catch(error => {
                    console.error('Error deleting data:', error);
                    alert('Gagal menghapus kelas!');
                });
            }
        }
    }
</script>
@endsection