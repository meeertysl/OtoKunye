<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerPortalSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $vehicleIds = $request->session()->get('customer_portal.vehicle_ids', []);
        if (! is_array($vehicleIds) || count($vehicleIds) === 0) {
            return redirect()->route('customer.portal.login')
                ->withErrors(['customer_portal' => 'Müşteri portalına devam etmek için tekrar giriş yapınız.']);
        }

        return $next($request);
    }
}
