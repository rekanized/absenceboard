<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AzureAuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AzureAuthentication
{
    public function __construct(private readonly AzureAuthenticationService $azureAuthentication)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->azureAuthentication->hasConfiguration() && User::query()->count() === 0) {
            return redirect()->route('setup.show');
        }

        $impersonatorUserId = $request->session()->get('impersonator_user_id');

        if ($impersonatorUserId !== null) {
            $impersonatorUser = is_numeric($impersonatorUserId)
                ? User::query()->active()->select(['id', 'is_admin'])->find((int) $impersonatorUserId)
                : null;

            if ($impersonatorUser === null || ! $impersonatorUser->is_admin) {
                $request->session()->forget(['current_user_id', 'impersonator_user_id']);

                return redirect()->guest(route('home'));
            }
        }

        $currentUserId = $request->session()->get('current_user_id');

        if (! is_numeric($currentUserId)) {
            $request->session()->forget(['current_user_id', 'impersonator_user_id']);

            return redirect()->guest(route('home'));
        }

        $currentUser = User::query()
            ->active()
            ->select(['id'])
            ->find((int) $currentUserId);

        if ($currentUser === null) {
            $request->session()->forget(['current_user_id', 'impersonator_user_id']);

            return redirect()->guest(route('home'));
        }

        return $next($request);
    }
}