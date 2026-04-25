<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl text-gray-800" style="font-family: 'Cormorant Garamond', serif;">Calendar</h2>
    </x-slot>
    <div class="py-8 max-w-5xl mx-auto px-4">
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Add event</h3>
            <form method="POST" action="{{ route('events.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-600">Title</label>
                    <input name="title" required class="mt-1 w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-xs text-gray-600">Department</label>
                    <select name="department_id" class="mt-1 w-full border-gray-300 rounded">
                        <option value="">— none —</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Start</label>
                    <input type="datetime-local" name="start_at" required class="mt-1 w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-xs text-gray-600">End (optional)</label>
                    <input type="datetime-local" name="end_at" class="mt-1 w-full border-gray-300 rounded" />
                </div>
                <div>
                    <label class="text-xs text-gray-600">Location</label>
                    <input name="location" class="mt-1 w-full border-gray-300 rounded" placeholder="Sanctuary" />
                </div>
                <div class="md:col-span-3">
                    <label class="text-xs text-gray-600">Notes</label>
                    <textarea name="notes" rows="2" class="mt-1 w-full border-gray-300 rounded"></textarea>
                </div>
                <div class="md:col-span-3 flex items-center justify-between">
                    <label class="text-sm inline-flex items-center gap-2">
                        <input type="checkbox" name="is_public" value="1" checked class="rounded"> Public (visible to non-members)
                    </label>
                    <button class="bg-gray-800 text-white rounded px-4 py-2 text-sm">Add event</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 text-sm text-gray-600 border-b">Upcoming & past</div>
            @forelse ($events as $e)
                <div class="p-4 border-b last:border-b-0 flex items-start gap-3">
                    <span class="mt-1 inline-block w-3 h-3 rounded-full ring-1 ring-black/10" style="background: {{ $e->department?->color_hex ?? '#9ca3af' }};"></span>
                    <div class="flex-1">
                        <div class="font-semibold">{{ $e->title }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $e->start_at->format('D, M j · g:i a') }}
                            @if ($e->location) · {{ $e->location }} @endif
                            @if ($e->department) · <span style="color: {{ $e->department->color_hex }}">{{ $e->department->name }}</span> @endif
                            · {{ $e->is_public ? 'Public' : 'Members only' }}
                        </div>
                        @if ($e->notes) <div class="mt-1 text-sm text-gray-600">{{ $e->notes }}</div> @endif
                    </div>
                    <form method="POST" action="{{ route('events.destroy', $e) }}" onsubmit="return confirm('Delete this event?');">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">No events yet.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
