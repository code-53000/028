<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => '未登录'], 401);
        }

        if ($request->user()->role !== $role && $request->user()->role !== 'admin') {
            return response()->json(['message' => '权限不足'], 403);
        }

        return $next($request);
    }
}
