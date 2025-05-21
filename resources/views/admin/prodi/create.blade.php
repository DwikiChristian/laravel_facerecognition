@extends('layouts.layout-admin')

@section('content')
<div x-data="prodiForm()" x-init="fetchJurusan()" class="max-w-md mt-10">
    <h2 class="text-2xl font-bold mb-4">Tambah Prodi</h2>

    <form @submit.prevent="submitForm" class="space-y-4">
        <div>
            <label for="jurusan_id" class="block mb-2 text-sm font-medium text-gray-900">Jurusan</label>
            <select x-model="jurusan_id" id="jurusan_id"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5">
                <option value="">Pilih Jurusan</option>
                <template x-for="jurusan in jurusans" :key="jurusan.id">
                    <option :value="jurusan.id" x-text="jurusan.nama"></option>
                </template>
            </select>
        </div>

        <div>
            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Nama Prodi</label>
            <input type="text" id="nama" x-model="nama"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                placeholder="Contoh: Teknik Informatika" required>
        </div>

        <div>
            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5">
                Simpan
            </button>
        </div>

        <template x-if="successMessage">
            <div class="text-green-600 mt-2" x-text="successMessage"></div>
        </template>

        <template x-if="errorMessage">
            <div class="text-red-600 mt-2" x-text="errorMessage"></div>
        </template>
    </form>
</div>
@endsection
@push('scripts')
<script>
    function prodiForm() {
        return {
            jurusans: [],
            jurusan_id: '',
            nama: '',
            successMessage: '',
            errorMessage: '',

            async fetchJurusan() {
                try {
                    const res = await fetch('/api/jurusans');
                    const data = await res.json();
                    this.jurusans = data;
                } catch (err) {
                    this.errorMessage = 'Gagal memuat jurusan.';
                }
            },

            async submitForm() {
                this.successMessage = '';
                this.errorMessage = '';

                try {
                    const res = await fetch('/api/prodis', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            jurusan_id: this.jurusan_id,
                            nama: this.nama
                        })
                    });

                    if (!res.ok) {
                        const error = await res.json();
                        this.errorMessage = error.message || 'Gagal menyimpan data.';
                        return;
                    }

                    const result = await res.json();
                    this.successMessage = `Prodi "${result.nama}" berhasil ditambahkan.`;
                    this.jurusan_id = '';
                    this.nama = '';
                } catch (err) {
                    this.errorMessage = 'Terjadi kesalahan. Coba lagi.';
                }
            }
        }
    }
</script>
@endpush