<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl text-gray-800" style="font-family: 'Cormorant Garamond', serif;">Edit bulletin</h2>
    </x-slot>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.min.css">
    <script type="module" src="https://cdn.jsdelivr.net/npm/trix@2.1.15/dist/trix.esm.min.js"></script>

    <div class="py-8 max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('bulletins.update', $bulletin) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-600">Title</label>
                        <input name="title" required class="mt-1 w-full border-gray-300 rounded" value="{{ old('title', $bulletin->title) }}" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-600">Service date</label>
                        <input type="date" name="service_date" required class="mt-1 w-full border-gray-300 rounded" value="{{ old('service_date', $bulletin->service_date->toDateString()) }}" />
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Bulletin content</label>
                    <input id="bulletin-body" type="hidden" name="body" value="{{ old('body', $bulletin->body) }}">
                    <trix-editor input="bulletin-body" class="mt-1 trix-content bg-white rounded border border-gray-300 min-h-[280px] px-3 py-2"></trix-editor>
                </div>
                <div class="flex items-center justify-between">
                    <label class="text-sm inline-flex items-center gap-2">
                        <input type="checkbox" name="is_published" value="1" @checked($bulletin->is_published) class="rounded"> Published
                    </label>
                    <div class="flex gap-2">
                        <a href="{{ route('bulletins.index') }}" class="text-sm text-gray-600 px-3 py-2">Cancel</a>
                        <button class="bg-gray-800 text-white rounded px-4 py-2 text-sm">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
