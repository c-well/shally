<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl text-gray-800" style="font-family: 'Cormorant Garamond', serif;">Departments</h2>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto px-4">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Add department</h3>
            <form method="POST" action="{{ route('departments.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-600">Name</label>
                    <input name="name" required class="mt-1 w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-xs text-gray-600">Color</label>
                    <input type="color" name="color_hex" value="#4f46e5" class="mt-1 w-full h-9 rounded cursor-pointer" />
                </div>
                <button class="bg-gray-800 text-white rounded px-4 py-2 text-sm">Add</button>
                <div class="md:col-span-4">
                    <label class="text-xs text-gray-600">Description (optional)</label>
                    <input name="description" class="mt-1 w-full border-gray-300 rounded" />
                </div>
                @error('name') <p class="md:col-span-4 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('color_hex') <p class="md:col-span-4 text-xs text-red-600">{{ $message }}</p> @enderror
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr><th class="p-3">Color</th><th class="p-3">Name</th><th class="p-3">Description</th><th class="p-3 text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($departments as $d)
                        <tr class="border-t">
                            <td class="p-3"><span class="inline-block w-5 h-5 rounded-full ring-1 ring-black/10" style="background: {{ $d->color_hex }};"></span></td>
                            <td class="p-3 font-medium">{{ $d->name }}</td>
                            <td class="p-3 text-gray-600">{{ $d->description }}</td>
                            <td class="p-3 text-right">
                                <form method="POST" action="{{ route('departments.destroy', $d) }}" class="inline" onsubmit="return confirm('Delete {{ $d->name }}?');">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-gray-500">No departments yet — add one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
