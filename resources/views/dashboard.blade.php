<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-medium text-gray-800 leading-tight" style="font-family: 'Cormorant Garamond', serif; font-size: 1.75rem;">
                Welcome, {{ Auth::user()->name }}
            </h2>
            <span class="text-xs uppercase tracking-[0.25em] text-gray-500">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <p class="mb-6 text-gray-600">Shalom — pick a section to manage.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('bulletins.index') }}" class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition group">
                    <div class="text-2xl text-gray-800 group-hover:text-indigo-700" style="font-family: 'Cormorant Garamond', serif;">Bulletins</div>
                    <p class="mt-2 text-sm text-gray-500">Upload the weekly bulletin PDF — service date, publish state, view &amp; download.</p>
                </a>
                <a href="{{ route('events.index') }}" class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition group">
                    <div class="text-2xl text-gray-800 group-hover:text-indigo-700" style="font-family: 'Cormorant Garamond', serif;">Calendar</div>
                    <p class="mt-2 text-sm text-gray-500">Upcoming events, department duties, color-coded.</p>
                </a>
                @if (Auth::user()->role === 'super_admin')
                <a href="{{ route('departments.index') }}" class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition group">
                    <div class="text-2xl text-gray-800 group-hover:text-indigo-700" style="font-family: 'Cormorant Garamond', serif;">Departments</div>
                    <p class="mt-2 text-sm text-gray-500">Ushers, Music, Children's, etc. Each department gets its own color.</p>
                </a>
                @endif
            </div>

            <div class="mt-10 text-xs text-gray-400 border-t pt-4">
                Public view of this app: <a class="text-gray-500 hover:underline" href="/" target="_blank">app.thechurchofpeace.org</a>
            </div>
        </div>
    </div>
</x-app-layout>
