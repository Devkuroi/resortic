<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Si ya hay sesión activa, redirigir al dashboard
        if (session()->has('user_id')) {
            return redirect()->route('accounts.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->verifyPassword($request->password)) {
            return back()->withErrors(['email' => 'Credenciales incorrectas. Verifica tu correo y contraseña.'])->withInput();
        }

        if ($user->status === 'inactive') {
            return back()->withErrors(['email' => 'Tu cuenta está inactiva. Contacta al administrador.'])->withInput();
        }

        // Guardar datos en sesión
        session([
            'user_id'   => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'user_email'=> $user->email,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Bienvenido, ' . $user->name . '.');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
