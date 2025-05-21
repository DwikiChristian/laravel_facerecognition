@extends('layouts.layout-admin')

@section('content')
<div x-data="jurusanForm()" class="max-w-md  mt-10">
    <h2 class="text-2xl font-bold mb-4">Tambah Jurusan</h2>

    <form @submit.prevent="submitForm" class="space-y-4">
        <div>
            <label for="nama" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Jurusan</label>
            <input type="text" id="nama" x-model="nama"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                placeholder="Jurusan..." required>
        </div>

        <div>
            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
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
        function jurusanForm() {
            return {
                nama: '',
                successMessage: '',
                errorMessage: '',
                async submitForm() {
                    this.successMessage = '';
                    this.errorMessage = '';
                    try {
                        const response = await fetch('/api/jurusans', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ nama: this.nama }),
                        });

                        if (!response.ok) {
                            const error = await response.json();
                            this.errorMessage = error.message || 'Gagal menyimpan data.';
                            return;
                        }

                        const data = await response.json();
                        this.successMessage = `Jurusan "${data.nama}" berhasil ditambahkan.`;
                        this.nama = '';
                    } catch (err) {
                        this.errorMessage = 'Terjadi kesalahan. Coba lagi.';
                    }
                }
            };
        }
    </script>
@endpush