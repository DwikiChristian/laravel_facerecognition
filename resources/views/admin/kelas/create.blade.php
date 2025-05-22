@extends('layouts.layout-admin')

@section('content')
<div x-data="kelasForm()" class="max-w-xl mt-10">
    <h2 class="text-2xl font-bold mb-4">Tambah Beberapa Kelas</h2>

    <form @submit.prevent="submitForm" class="space-y-4">
        <div>
            <label class="block mb-2 text-sm font-medium text-gray-900">Prodi</label>
            <select x-model="prodi_id"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5" required>
                <option value="" disabled selected>Pilih Prodi</option>
                <template x-for="prodi in prodis" :key="prodi.id">
                    <option :value="prodi.id" x-text="prodi.nama + ' - ' + prodi.jurusan?.nama"></option>
                </template>
            </select>
        </div>

        <template x-for="(kelas, index) in kelasList" :key="index">
            <div class="flex items-center space-x-2">
                <input type="text" x-model="kelasList[index].nama"
                    class="w-full bg-gray-50 border border-gray-300 text-sm rounded-lg p-2.5"
                    :placeholder="`Nama Kelas ${index + 1}`" required>
                <button type="button" @click="removeKelas(index)"
                    class="text-red-500 hover:text-red-700 text-lg font-bold">×</button>
            </div>
        </template>

        <div>
            <button type="button" @click="addKelas"
                class="text-blue-700 font-semibold hover:underline">+ Tambah Kelas</button>
        </div>

        <button type="submit"
            class="bg-blue-700 text-white rounded-lg px-5 py-2.5 hover:bg-blue-800">Simpan Semua</button>

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
    function kelasForm() {
        return {
            prodis: [],
            prodi_id: '',
            kelasList: [{ nama: '' }],
            successMessage: '',
            errorMessage: '',

            async fetchProdis() {
                try {
                    const response = await fetch('/api/prodis');
                    const data = await response.json();
                    this.prodis = data;
                } catch (err) {
                    console.error('Gagal memuat prodi:', err);
                }
            },

            addKelas() {
                this.kelasList.push({ nama: '' });
            },

            removeKelas(index) {
                this.kelasList.splice(index, 1);
            },

            async submitForm() {
                this.successMessage = '';
                this.errorMessage = '';

                try {
                    for (const kelas of this.kelasList) {
                        const response = await fetch('/api/kelas', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                prodi_id: this.prodi_id,
                                nama: kelas.nama,
                            }),
                        });

                        if (!response.ok) {
                            const error = await response.json();
                            this.errorMessage = error.message || 'Gagal menyimpan salah satu kelas.';
                            return;
                        }
                    }

                    this.successMessage = 'Semua kelas berhasil disimpan!';
                    this.kelasList = [{ nama: '' }];
                } catch (err) {
                    this.errorMessage = 'Terjadi kesalahan saat menyimpan kelas.';
                }
            },

            init() {
                this.fetchProdis();
            }
        }
    }
</script>
@endpush
