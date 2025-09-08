<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateUser
{
    public function handle(Request $request, Closure $next)
    {
        $impersonatedUserId = $request->session()->get('impersonated_user_id');
        $adminUserId = $request->session()->get('impersonated_by_admin_id');

        if ($impersonatedUserId && $adminUserId) {
            $targetUser = User::find($impersonatedUserId);
            if ($targetUser) {
                // Swap the effective authenticated user for this request only
                Auth::setUser($targetUser);

                // Enforce read-only: block non-safe HTTP methods
                if (!in_array($request->getMethod(), ['GET', 'HEAD'])) {
                    $message = 'Viewing as user (read-only). Changes are disabled.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()
                            ->json(['message' => $message], 403)
                            ->withHeaders([
                                'X-Impersonation-Blocked' => '1',
                                'X-Impersonation-Message' => $message,
                            ]);
                    }
                    return redirect()->back()->with('impersonation_block', $message);
                }

                // Share banner context with all views
                $adminUser = User::find($adminUserId);
                view()->share('impersonation', [
                    'target' => $targetUser,
                    'admin' => $adminUser,
                    'mode' => 'read_only',
                ]);
            } else {
                // If target user no longer exists, clear impersonation
                $request->session()->forget([
                    'impersonated_user_id',
                    'impersonated_by_admin_id',
                    'impersonation_mode',
                ]);
            }
        }

        return $next($request);
    }
}


