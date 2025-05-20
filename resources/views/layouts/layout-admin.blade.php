<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <!-- Scripts -->
     @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex">
    {{-- Sidebar --}}
    @include('partials.admin.sidebar')

    <div class="flex-1">
        {{-- Navbar --}}
        @include('partials.admin.navbar')

        {{-- Konten --}}
        <main class="p-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
