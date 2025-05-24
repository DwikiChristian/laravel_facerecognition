@extends('layouts.layout-admin')

@section('content')
<div x-data="matakuliahForm()" class="max-w-xl mx-auto mt-10 space-y-6">

    <h2 class="text-2xl font-bold text-gray-800">Tambah Mata Kuliah</h2>

    <!-- Nama Mata Kuliah -->
    <div>
        <label for="nama" class="block mb-2 text-sm font-medium text-gray-900">Nama Mata Kuliah</label>
        <input type="text" id="nama" x-model="nama" placeholder="Contoh: Pemrograman Web"
            class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500" />
    </div>

    <!-- Kode Mata Kuliah -->
    <div>
        <label for="kode" class="block mb-2 text-sm font-medium text-gray-900">Kode Mata Kuliah</label>
        <input type="text" id="kode" x-model="kode" placeholder="Contoh: IF123"
            class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500" />
    </div>

    <!-- Submit -->
    <div>
        <button @click="submitForm"
            class="text-white bg-blue-700 hover:bg-blue-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
            Simpan
        </button>
    </div>

    <!-- Alert -->
    <div x-show="successMessage" class="text-green-600 font-medium" x-text="successMessage"></div>
    <div x-show="errorMessage" class="text-red-600 font-medium" x-text="errorMessage"></div>
</div>
@endsection

@push('scripts')
<script>
    function matakuliahForm() {
        return {
            nama: '',
            kode: '',
            successMessage: '',
            errorMessage: '',

            async submitForm() {
                this.successMessage = '';
                this.errorMessage = '';

                try {
                    const response = await fetch('/api/matakuliah', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            nama: this.nama,
                            kode: this.kode,
                        }),
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        this.errorMessage = Object.values(result.errors || {}).flat().join(', ');
                        return;
                    }

                    this.successMessage = result.message;
                    this.nama = '';
                    this.kode = '';
                } catch (e) {
                    this.errorMessage = 'Gagal mengirim data.';
                }
            }
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('matakuliahForm', matakuliahForm);
    });
</script>
@endpush
