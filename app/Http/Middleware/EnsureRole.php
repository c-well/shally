<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) abort(403);

        $allowed = collect($roles)->flatMap(fn($r) => array_merge([$r], match($r) {
            'super_admin' => [],
            'clerk', 'department_head' => ['super_admin'],
            'member' => ['super_admin', 'clerk', 'department_head'],
            default => [],
        }))->unique()->all();

        if (! in_array($user->role, $allowed, true)) abort(403);
        return $next($request);
    }
}
