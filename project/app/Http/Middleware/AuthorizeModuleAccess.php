<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeModuleAccess
{
    private const RESOURCES = [
        'users' => 'users',
        'roles' => 'roles',
        'access' => 'roles',
        'leagues' => 'leagues',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        [$module, $action] = explode('.', $request->route()->getName() ?? '', 2) + [1 => null];
        $resource = self::RESOURCES[$module] ?? null;

        abort_unless($user->is_active && $resource !== null, 403);

        $permission = match ($action) {
            'create', 'store' => 'create',
            'destroy' => 'delete',
            'edit', 'update', 'process' => 'write',
            default => $request->isMethodSafe() ? 'read' : 'write',
        };
        abort_unless($user->canAccessAction($resource, $permission), 403);

        return $next($request);
    }
}
