<?php
namespace App\Http\Controllers;

use App\Models\AdminNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Notes & Keys — the admin's private drawer. Encrypted bodies, soft deletes. */
class AdminNotesController extends Controller
{
    public function index(): View
    {
        return view('admin.notes', ['notes' => AdminNote::orderByDesc('updated_at')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['title' => 'required|string|max:120', 'body' => 'required|string|max:8000']);
        $n = AdminNote::create($data + ['created_by' => $request->user()->id]);
        return response()->json(['ok' => true, 'id' => $n->id]);
    }

    public function update(Request $request, AdminNote $note): JsonResponse
    {
        $data = $request->validate(['title' => 'sometimes|string|max:120', 'body' => 'sometimes|string|max:8000']);
        $note->update($data);
        return response()->json(['ok' => true]);
    }

    public function destroy(AdminNote $note): JsonResponse
    {
        $note->delete();
        return response()->json(['ok' => true]);
    }
}
