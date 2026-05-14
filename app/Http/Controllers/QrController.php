<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrController extends Controller
{
    public function scan(Request $request, string $uuid): RedirectResponse
    {
        $vehicle = Vehicle::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($request->boolean('master')) {
            if (Auth::check()) {
                $currentUser = Auth::user();
                $isAdmin = $currentUser instanceof \App\Models\User && $currentUser->isAdmin();
                if ($vehicle->owner_user_id !== Auth::id() && ! $isAdmin) {
                    return redirect()->route('vehicles.index')
                        ->withErrors(['vehicle' => 'Bu araca erişim yetkiniz bulunmuyor.']);
                }
                return redirect()->route('vehicles.show', $vehicle);
            }

            return redirect()->guest(route('vehicles.show', $vehicle));
        }

        if (Auth::check() && ! $request->boolean('preview')) {
            $currentUser = Auth::user();
            $isAdmin = $currentUser instanceof \App\Models\User && $currentUser->isAdmin();
            if ($vehicle->owner_user_id !== Auth::id() && ! $isAdmin) {
                return redirect()->route('vehicles.index')
                    ->withErrors(['vehicle' => 'Bu araca erişim yetkiniz bulunmuyor.']);
            }
            return redirect()->route('vehicles.show', $vehicle);
        }

        return redirect()->route('vehicles.public.show', $vehicle->uuid);
    }
}
