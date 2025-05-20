<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mahasiswa Dashboard</title>
     @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex">
    {{-- Sidebar --}}
    @include('partials.mahasiswa.sidebar')

    <div class="flex-1">
        {{-- Navbar --}}
        @include('partials.mahasiswa.navbar')

        {{-- Konten --}}
        <main class="p-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
