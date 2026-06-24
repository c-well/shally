<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl text-gray-800" style="font-family: 'Cormorant Garamond', serif;">Bulletins</h2>
    </x-slot>
    {{-- Trix rich-text editor --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.min.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.esm.min.js"></script>

    <div class="py-8 max-w-4xl mx-auto px-4">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">New bulletin</h3>
            <form method="POST" action="{{ route('bulletins.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-600">Title</label>
                        <input name="title" required class="mt-1 w-full border-gray-300 rounded" placeholder="e.g. Sabbath Service — April 25" value="{{ old('title') }}" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Service date</label>
                        <input type="date" name="service_date" required class="mt-1 w-full border-gray-300 rounded" value="{{ old('service_date') }}" />
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Bulletin content</label>
                    <input id="bulletin-body" type="hidden" name="body" value="{{ old('body') }}">
                    <trix-editor input="bulletin-body" class="mt-1 trix-content bg-white rounded border border-gray-300 min-h-[280px] px-3 py-2"></trix-editor>
                    <p class="mt-1 text-xs text-gray-500">Tip: you can paste from Word / Google Docs and basic formatting will carry over.</p>
                </div>
                <div class="flex items-center justify-between">
                    <label class="text-sm inline-flex items-center gap-2">
                        <input type="checkbox" name="is_published" value="1" checked class="rounded"> Publish (visible on public page)
                    </label>
                    <button class="bg-gray-800 text-white rounded px-4 py-2 text-sm">Save bulletin</button>
                </div>
                @error('title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('service_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('body') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </form>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 text-sm text-gray-600 border-b">All bulletins</div>
            @forelse ($bulletins as $b)
                <div class="p-4 border-b last:border-b-0 flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold truncate">{{ $b->title }}</div>
                        <div class="text-xs text-gray-500">{{ $b->service_date->format('M j, Y') }} · {{ $b->is_published ? 'Published' : 'Draft' }}</div>
                    </div>
                    <a href="{{ route('bulletins.show', $b) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">View</a>
                    <a href="{{ route('bulletins.edit', $b) }}" class="text-xs text-gray-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('bulletins.destroy', $b) }}" data-confirm="Delete this bulletin?">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">No bulletins yet — create one above.</div>
            @endforelse
        </div>
    </div>
@include('partials._confirm')
</x-app-layout>
