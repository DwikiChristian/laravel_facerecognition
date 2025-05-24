@extends('layouts.layout-admin')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow mt-6" x-data="jadwalForm()" x-init="init()">
    <h2 class="text-2xl font-bold mb-4">Tambah Jadwal</h2>

    <form @submit.prevent="submitForm">
        {{-- Jurusan --}}
        <div class="mb-4">
            <label for="jurusan_id" class="block mb-2 text-sm font-medium text-gray-900">Jurusan</label>
            <select x-model="selectedJurusanId" id="jurusan_id" class="form-select w-full">
                <option value="">-- Pilih Jurusan --</option>
                <template x-for="jurusan in jurusanList" :key="jurusan.id">
                    <option :value="jurusan.id" x-text="jurusan.nama"></option>
                </template>
            </select>
        </div>

        {{-- Prodi --}}
        <div class="mb-4">
            <label for="prodi_id" class="block mb-2 text-sm font-medium text-gray-900">Prodi</label>
            <select x-model="selectedProdiId" id="prodi_id" class="form-select w-full">
                <option value="">-- Pilih Prodi --</option>
                <template x-for="prodi in filteredProdiList" :key="prodi.id">
                    <option :value="prodi.id" x-text="prodi.nama"></option>
                </template>
            </select>
        </div>

        {{-- Kelas --}}
        <div class="mb-4">
            <label for="kelas_id" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
            <select x-model="form.kelas_id" id="kelas_id" class="form-select w-full">
                <option value="">-- Pilih Kelas --</option>
                <template x-for="kelas in filteredKelasList" :key="kelas.id">
                    <option :value="kelas.id" x-text="kelas.nama + ' - ' + kelas.prodi.nama"></option>
                </template>
            </select>
        </div>

        {{-- Dosen --}}
        <div class="mb-4">
            <label for="dosen_id" class="block mb-2 text-sm font-medium text-gray-900">Dosen</label>
            <select x-model="form.dosen_id" id="dosen_id" class="form-select w-full">
                <option value="">-- Pilih Dosen --</option>
                <template x-for="dosen in dosenList" :key="dosen.id">
                    <option :value="dosen.id" x-text="dosen.nama"></option>
                </template>
            </select>
        </div>

        {{-- Mata Kuliah --}}
        <div class="mb-4">
            <label for="mata_kuliah_id" class="block mb-2 text-sm font-medium text-gray-900">Mata Kuliah</label>
            <select x-model="form.mata_kuliah_id" id="mata_kuliah_id" class="form-select w-full">
                <option value="">-- Pilih Mata Kuliah --</option>
                <template x-for="mk in matkulList" :key="mk.id">
                    <option :value="mk.id" x-text="mk.nama"></option>
                </template>
            </select>
        </div>

        {{-- Hari --}}
        <div class="mb-4">
            <label for="hari" class="block mb-2 text-sm font-medium text-gray-900">Hari</label>
            <select x-model="form.hari" id="hari" class="form-select w-full">
                <option value="">-- Pilih Hari --</option>
                <template x-for="hari in hariList" :key="hari">
                    <option :value="hari" x-text="hari"></option>
                </template>
            </select>
        </div>

        {{-- Jam Mulai --}}
        <div class="mb-4">
            <label for="jam_mulai" class="block mb-2 text-sm font-medium text-gray-900">Jam Mulai</label>
            <input type="time" id="jam_mulai" x-model="form.jam_mulai" class="form-input w-full">
        </div>

        {{-- Jam Selesai --}}
        <div class="mb-4">
            <label for="jam_selesai" class="block mb-2 text-sm font-medium text-gray-900">Jam Selesai</label>
            <input type="time" id="jam_selesai" x-model="form.jam_selesai" class="form-input w-full">
        </div>

        <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Simpan Jadwal
        </button>
    </form>
</div>

<script>
    function jadwalForm() {
        return {
            form: {
                kelas_id: '',
                dosen_id: '',
                mata_kuliah_id: '',
                hari: '',
                jam_mulai: '',
                jam_selesai: '',
            },
            selectedJurusanId: '',
            selectedProdiId: '',
            jurusanList: [],
            prodiList: [],
            kelasList: [],
            dosenList: [],
            matkulList: [],
            hariList: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],

            get filteredProdiList() {
                return this.prodiList.filter(p => p.jurusan_id == this.selectedJurusanId);
            },

            get filteredKelasList() {
                return this.kelasList.filter(k => k.prodi_id == this.selectedProdiId);
            },

            async fetchData() {
                const [jurusan, prodi, kelas, dosen, matkul] = await Promise.all([
                    fetch('/api/jurusans').then(res => res.json()),
                    fetch('/api/prodis').then(res => res.json()),
                    fetch('/api/kelas').then(res => res.json()),
                    fetch('/api/dosens').then(res => res.json()),
                    fetch('/api/matakuliah').then(res => res.json())
                ]);

                this.jurusanList = jurusan;
                this.prodiList = prodi;
                this.kelasList = kelas;
                this.dosenList = dosen;
                this.matkulList = matkul;
            },

            async submitForm() {
                try {
                    const res = await fetch('/api/jadwal', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert('Gagal simpan: ' + JSON.stringify(data.errors));
                        return;
                    }

                    alert('Jadwal berhasil ditambahkan!');
                    window.location.href = '/admin/jadwal';
                } catch (err) {
                    console.error(err);
                    alert('Terjadi kesalahan.');
                }
            },

            init() {
                this.fetchData();
            }
        }
    }
</script>
@endsection
