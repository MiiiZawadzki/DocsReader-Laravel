<?php

namespace Modules\Engagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The SPA's final flush uses navigator.sendBeacon, which can't send application/json cross-origin
 * (it would need a CORS preflight that beacons never make), so it sends the JSON body as text/plain
 */
class DecodeBeaconPayload
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (str_starts_with((string) $request->header('Content-Type'), 'text/plain')) {
            $payload = json_decode($request->getContent(), true);

            if (is_array($payload)) {
                $request->request->replace($payload);
            }
        }

        return $next($request);
    }
}
