@extends('layouts.layout-admin')

@section('content')
<div x-data="mahasiswaForm()" class="max-w-4xl mx-auto mt-10 space-y-6">

    <h2 class="text-2xl font-bold">Register Mahasiswa</h2>

    <!-- Select Jurusan -->
    <div>
        <label for="jurusan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jurusan</label>
        <select id="jurusan" x-model="jurusan_id" @change="fetchProdis()" class="form-select w-full">
            <option value="">Pilih Jurusan</option>
            <template x-for="j in jurusans" :key="j.id">
                <option :value="j.id" x-text="j.nama"></option>
            </template>
        </select>
    </div>

    <!-- Select Prodi -->
    <div>
        <label for="prodi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prodi</label>
        <select id="prodi" x-model="prodi_id" @change="fetchKelas()" class="form-select w-full">
            <option value="">Pilih Prodi</option>
            <template x-for="p in prodis" :key="p.id">
                <option :value="p.id" x-text="p.nama"></option>
            </template>
        </select>
    </div>

    <!-- Select Kelas -->
    <div>
        <label for="kelas" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kelas</label>
        <select id="kelas" x-model="kelas_id" class="form-select w-full">
            <option value="">Pilih Kelas</option>
            <template x-for="k in kelasList" :key="k.id">
                <option :value="k.id" x-text="k.nama"></option>
            </template>
        </select>
    </div>

    <!-- Form Dinamis Mahasiswa -->
    <template x-for="(mhs, index) in mahasiswa" :key="index">
        <div class="p-4 bg-gray-100 rounded-lg shadow space-y-2 relative">
            <h4 class="font-semibold text-gray-700">Mahasiswa <span x-text="index + 1"></span></h4>

            <div>
                <label class="block mb-1 text-sm font-medium">Nama</label>
                <input type="text" x-model="mhs.nama" placeholder="Nama Mahasiswa"
                    class="form-input w-full" />
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">NIM</label>
                <input type="text" x-model="mhs.nim" placeholder="NIM"
                    class="form-input w-full" />
            </div>

            <button type="button" @click="removeMahasiswa(index)"
                class="absolute top-2 right-2 text-red-600 text-sm hover:underline">Hapus</button>
        </div>
    </template>

    <!-- Tombol Tambah Mahasiswa -->
    <div>
        <button type="button" @click="addMahasiswa"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Tambah Mahasiswa</button>
    </div>

    <!-- Submit -->
    <div>
        <button @click="submitForm"
            class="px-6 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">Simpan</button>
    </div>

    <!-- Alert -->
    <div class="text-green-600 font-medium" x-text="successMessage"></div>
    <div class="text-red-600 font-medium" x-text="errorMessage"></div>
</div>
@endsection

@push('scripts')
<script>
    function mahasiswaForm() {
        return {
            jurusan_id: '',
            prodi_id: '',
            kelas_id: '',
            jurusans: [],
            prodis: [],
            kelasList: [],
            mahasiswa: [
                { nama: '', nim: '' }
            ],
            successMessage: '',
            errorMessage: '',

            async fetchJurusans() {
                const res = await fetch('/api/jurusans');
                const data = await res.json();
                this.jurusans = data;
            },

            async fetchProdis() {
                this.prodi_id = '';
                this.kelas_id = '';
                this.prodis = [];
                this.kelasList = [];

                if (!this.jurusan_id) return;

                const res = await fetch(`/api/prodis?jurusan_id=${this.jurusan_id}`);
                const data = await res.json();
                this.prodis = data;
            },

            async fetchKelas() {
                this.kelas_id = '';
                this.kelasList = [];

                if (!this.prodi_id) return;

                const res = await fetch(`/api/kelas?prodi_id=${this.prodi_id}`);
                const data = await res.json();
                this.kelasList = data;
            },

            addMahasiswa() {
                this.mahasiswa.push({ nama: '', nim: '' });
            },

            removeMahasiswa(index) {
                this.mahasiswa.splice(index, 1);
            },

            async submitForm() {
                this.successMessage = '';
                this.errorMessage = '';

                try {
                    const response = await fetch('/api/mahasiswa', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            kelas_id: this.kelas_id,
                            mahasiswa: this.mahasiswa
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        console.error('Error response:', result); 
                        this.errorMessage = result?.errors ?? 'Terjadi kesalahan';
                        return;
                    }

                    this.successMessage = result.message;
                    this.mahasiswa = [{ nama: '', nim: '' }];
                    this.kelas_id = '';
                } catch (e) {
                    this.errorMessage = 'Gagal mengirim data.';
                }
            },

            async init() {
                await this.fetchJurusans();
            }
        };
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('mahasiswaForm', mahasiswaForm);
    });
</script>
@endpush
