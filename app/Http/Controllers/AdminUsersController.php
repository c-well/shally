<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    /** GET /admin/users — list + add user form (super_admin only). */
    public function index(): View
    {
        $users = User::orderBy('role', 'desc')->orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    /** POST /admin/users — create a new user with optional PIN. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:80|unique:users,name',
            'email' => ['nullable', 'email', 'max:120', Rule::unique('users', 'email')],
            'role'  => ['required', Rule::in(['sabbath', 'clerk', 'super_admin'])],
            'pin'   => ['nullable', 'string', 'min:4', 'max:8', 'regex:/^\d+$/'],
        ]);

        $user = new User();
        $user->name  = trim($data['name']);
        $user->email = $data['email'] ?: null;
        $user->role  = $data['role'];
        // Random temporary password — they'll use PIN/magic-link to sign in, not password
        $user->password = Hash::make(\Illuminate\Support\Str::random(32));
        if (! empty($data['pin'])) {
            $user->pin_hash = Hash::make($data['pin']);
        }
        $user->save();

        AuditLog::record(
            event: 'user_created',
            userId: $request->user()->id,
            description: $request->user()->name . " created user '{$user->name}' (role: {$user->role})" . (!empty($data['pin']) ? ' with PIN' : ''),
            meta: ['new_user_id' => $user->id, 'role' => $user->role, 'has_pin' => !empty($data['pin'])],
        );

        $flash = "Created {$user->name}.";
        if (! empty($data['pin'])) {
            $flash .= " Their PIN is {$data['pin']} — write it down, you won't see it again.";
        }
        return back()->with('status', $flash);
    }

    /** PATCH /admin/users/{user}/pin — set or reset a user's PIN. */
    public function updatePin(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:8', 'regex:/^\d+$/'],
        ]);

        $user->pin_hash = Hash::make($data['pin']);
        $user->save();

        AuditLog::record(
            event: 'pin_assigned',
            userId: $request->user()->id,
            description: $request->user()->name . " set PIN for '{$user->name}'",
            meta: ['target_user_id' => $user->id],
        );

        return back()->with('status', "{$user->name}'s PIN is {$data['pin']} — write it down, you won't see it again.");
    }

    /** DELETE /admin/users/{user}/pin — clear a user's PIN. */
    public function clearPin(Request $request, User $user): RedirectResponse
    {
        $user->pin_hash = null;
        $user->save();

        AuditLog::record(
            event: 'pin_cleared',
            userId: $request->user()->id,
            description: $request->user()->name . " cleared PIN for '{$user->name}'",
            meta: ['target_user_id' => $user->id],
        );

        return back()->with('status', "Cleared PIN for {$user->name}.");
    }
}
