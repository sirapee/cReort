<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CORS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        header('Access-control-Allow-Origin: *');
        //header('Access-control-Allow-Headers: Accept-Encoding, User-Agent, Host, Content-type, X-Auth-Token, Authorization, Origin, X-Requested-With, Connection');
        header('Access-control-Allow-Headers: *');
        return $next($request);
    }
}
