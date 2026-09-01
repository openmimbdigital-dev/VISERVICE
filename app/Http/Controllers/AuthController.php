<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->hasRole('superAdmin')) {
                CurrentBusiness::initializeForUser($user);
                $business = CurrentBusiness::get() ?? $user->primaryBusiness();

                if ($business === null || ! $business->hasActiveSubscription()) {
                    return redirect()->route('pending-activation');
                }
            }

            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'El username es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $identifier = trim((string) $request->input('username'));
        $remember = $request->boolean('remember');

        $credentials = [
            'username' => $identifier,
            'password' => $request->input('password'),
        ];

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $request->session()->regenerate();

        if (isset($user->status) && ! $user->status) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        CurrentBusiness::initializeForUser($user);

        if (! $user->hasRole('superAdmin') && ! $this->userBusinessHasConfirmedAccess($user)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Tu pago aún no ha sido confirmado. Podrás iniciar sesión cuando el administrador valide el pago en el módulo de pagos.',
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }

    private function userBusinessHasConfirmedAccess(User $user): bool
    {
        $business = CurrentBusiness::get() ?? $user->primaryBusiness();

        return $business !== null && $business->hasActiveSubscription();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Has cerrado sesión exitosamente.');
    }
}
