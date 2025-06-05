<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryPersonMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isDeliveryPerson()) {
            abort(403, 'Acesso negado. Apenas entregadores podem acessar esta área.');
        }

        return $next($request);
    }
}
