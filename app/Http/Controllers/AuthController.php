<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('reservations.availability');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Credenciales incorrectas. Verifica tu correo y contraseña.'])
                ->withInput();
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            return back()
                ->withErrors(['email' => 'Tu cuenta está inactiva. Contacta al administrador.'])
                ->withInput();
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('reservations.availability'))
            ->with('success', "Bienvenido, {$user->name}.");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
