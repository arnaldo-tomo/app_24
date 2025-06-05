<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isRestaurantOwner()) {
            abort(403, 'Acesso negado. Apenas proprietários de restaurantes podem acessar esta área.');
        }

        return $next($request);
    }
}
