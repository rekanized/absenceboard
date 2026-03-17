<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentUserId = $request->session()->get('current_user_id');

        if ($currentUserId && User::query()->whereKey($currentUserId)->exists()) {
            return $next($request);
        }

        $firstUserId = User::query()->orderBy('id')->value('id');

        if ($firstUserId !== null) {
            $request->session()->put('current_user_id', (int) $firstUserId);
        }

        return $next($request);
    }
}
