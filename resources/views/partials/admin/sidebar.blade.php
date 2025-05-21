<aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700" aria-label="Sidebar">
   <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">
      <ul class="space-y-2 font-medium">
         <li>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M10 20v-6h4v6h5v-8h3L10 0 2 12h3v8z"/></svg>
                  <span class="ms-3">Dashboard</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.jurusan.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4a2 2 0 012-2h12a2 2 0 012 2v1H2V4zm0 3h16v9a2 2 0 01-2 2H4a2 2 0 01-2-2V7z"/></svg>
                  <span class="ms-3">Jurusan</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.prodi.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3a1 1 0 000 2h14a1 1 0 100-2H3zM3 7a1 1 0 000 2h14a1 1 0 100-2H3zM3 11a1 1 0 000 2h14a1 1 0 100-2H3z"/></svg>
                  <span class="ms-3">Program Studi</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.jadwal.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z"/></svg>
                  <span class="ms-3">Jadwal</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.kelas.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z"/></svg>
                  <span class="ms-3">Kelas</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.presensi.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z"/></svg>
                  <span class="ms-3">Presensiw</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.dosen.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 13a3 3 0 11-6 0 3 3 0 016 0zM4 13a3 3 0 116 0 3 3 0 01-6 0zm7.5-3.5A4.5 4.5 0 1013 2a4.5 4.5 0 00-1.5 7.5zM2 18a6 6 0 0112 0H2zm14.25 0a7.5 7.5 0 00-14.5 0H16.25z" clip-rule="evenodd"/></svg>
                  <span class="ms-3">Dosen</span>
            </a>
         </li>
         <li>
            <a href="{{ route('admin.mahasiswa.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                  <svg class="w-5 h-5 text-gray-500" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 13a3 3 0 11-6 0 3 3 0 016 0zM4 13a3 3 0 116 0 3 3 0 01-6 0zm7.5-3.5A4.5 4.5 0 1013 2a4.5 4.5 0 00-1.5 7.5zM2 18a6 6 0 0112 0H2zm14.25 0a7.5 7.5 0 00-14.5 0H16.25z" clip-rule="evenodd"/></svg>
                  <span class="ms-3">Mahasiswa</span>
            </a>
         </li>
      </ul>

   </div>
</aside>