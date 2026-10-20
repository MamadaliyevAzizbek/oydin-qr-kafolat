<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelPage
{
    public function handle(Request $request, Closure $next, string $page): Response
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessPage($page)) {
            return response()->json(['message' => 'Bu boʼlimga ruxsat yoʼq.'], 403);
        }

        return $next($request);
    }
}
