@extends('layouts.layout-admin')

@section('content')
<div x-data="dosenForm()" class="max-w-3xl mx-auto mt-10 space-y-6">

    <h2 class="text-2xl font-bold">Register Dosen</h2>

    <!-- Form Dinamis Dosen -->
    <template x-for="(dosen, index) in dosens" :key="index">
        <div class="p-4 bg-gray-100 rounded-lg shadow space-y-2 relative">
            <h4 class="font-semibold text-gray-700">Dosen <span x-text="index + 1"></span></h4>

            <div>
                <label class="block mb-1 text-sm font-medium">Nama</label>
                <input type="text" x-model="dosen.nama" placeholder="Nama Dosen"
                    class="form-input w-full" />
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">NIDN</label>
                <input type="text" x-model="dosen.nidn" placeholder="NIDN"
                    class="form-input w-full" />
            </div>

            <button type="button" @click="removeDosen(index)"
                class="absolute top-2 right-2 text-red-600 text-sm hover:underline">Hapus</button>
        </div>
    </template>

    <!-- Tombol Tambah -->
    <div>
        <button type="button" @click="addDosen"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Tambah Dosen</button>
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
    function dosenForm() {
        return {
            dosens: [
                { nama: '', nidn: '' }
            ],
            successMessage: '',
            errorMessage: '',

            addDosen() {
                this.dosens.push({ nama: '', nidn: '' });
            },

            removeDosen(index) {
                this.dosens.splice(index, 1);
            },

            async submitForm() {
                this.successMessage = '';
                this.errorMessage = '';

                try {
                    const response = await fetch('/api/dosens', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            dosens: this.dosens
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        this.errorMessage = result?.errors ?? 'Terjadi kesalahan';
                        return;
                    }

                    this.successMessage = result.message;
                    this.dosens = [{ nama: '', nidn: '' }];
                } catch (e) {
                    this.errorMessage = 'Gagal mengirim data.';
                }
            }
        }
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('dosenForm', dosenForm);
    });
</script>
@endpush
