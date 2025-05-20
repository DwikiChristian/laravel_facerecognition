<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosen Dashboard</title>
    
    <!-- Critical inline styles to prevent layout shifts -->
    <style>
        /* Preloader to hide content until fully loaded */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        /* Initial navbar styles to maintain consistency */
        .mobile-nav {
            display: flex;
            align-items: center;
            height: 56px; /* Fixed height to prevent shifts */
            width: 100%;
        }
        
        .mobile-nav .brand {
            display: flex;
            align-items: center;
        }
        
        /* Hide content until loaded */
        .content-container {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        /* Loaded state */
        .loaded .content-container {
            opacity: 1;
        }
        
        .loaded .preloader {
            display: none;
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Preloader -->
    <div class="preloader">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <div class="content-container flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        @include('partials.mahasiswa.sidebar')

        <div class="flex flex-col flex-1 w-full">
            @include('partials.mahasiswa.navbar')

            {{-- Main Content - Added pt-16 to create space for navbar and ml-64 for sidebar --}}
            <main class="flex-1 overflow-y-auto p-4 pt-16 sm:ml-64">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Script to handle loading state -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for all resources to load
            window.addEventListener('load', function() {
                // Add a small delay to ensure all CSS is applied
                setTimeout(function() {
                    document.body.classList.add('loaded');
                }, 300);
            });
        });
    </script>
</body>
</html>