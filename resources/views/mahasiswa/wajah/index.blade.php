@extends('layouts.layout-mahasiswa')

@section('content')
<div class="max-w-xl mx-auto mt-10 bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-semibold mb-4 text-center">Upload Wajah</h2>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mahasiswa.mahasiswa.upload') }}" method="POST" enctype="multipart/form-data" x-data="{ files: [] }">
        @csrf
        <input type="hidden" name="name" value="{{ Auth::user()->name }}">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="foto">
                Pilih Foto Wajah (bisa lebih dari satu)
            </label>
            <input 
                type="file" 
                name="foto[]" 
                id="foto" 
                accept="image/*" 
                multiple 
                @change="files = Array.from($event.target.files)"
                class="border border-gray-300 p-2 w-full rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <template x-if="files.length">
                <ul class="mt-2 list-disc list-inside text-sm text-gray-600">
                    <template x-for="file in files" :key="file.name">
                        <li x-text="file.name"></li>
                    </template>
                </ul>
            </template>
        </div>

        <div class="flex justify-center">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
                Upload
            </button>
        </div>
    </form>

</div>
@endsection
