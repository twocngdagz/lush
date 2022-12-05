<?php

namespace App\Services\OriginConnector\Providers\Lush\Middleware;

use Closure;

class LushCMS
{
    public function handle($request, Closure $next)
    {
        if (isCMSLush()) {
            return $next($request);
        }
        return redirect('/dashboard');
    }
}
