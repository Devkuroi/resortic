<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    // Middleware manual: verificar sesión en cada método
    private function checkSession()
    {
        if (!session()->has('user_id')) {
            abort(redirect()->route('login')->with('error', 'Debes iniciar sesión.'));
        }
    }

    public function index(Request $request)
    {
        $this->checkSession();

        $query = User::query();

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accounts = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Estadísticas
        $stats = [
            'total'    => User::count(),
            'active'   => User::where('status', 'active')->count(),
            'hotels'   => User::where('role', 'hotel')->count(),
            'clients'  => User::where('role', 'client')->count(),
        ];

        return view('accounts.index', compact('accounts', 'stats'));
    }

    public function create()
    {
        $this->checkSession();
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $this->checkSession();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:admin,hotel,client',
            'status'   => 'required|in:active,inactive',
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required'      => 'El rol es obligatorio.',
            'status.required'    => 'El estado es obligatorio.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => $request->status,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Cuenta creada exitosamente.');
    }

    public function edit(User $account)
    {
        $this->checkSession();
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, User $account)
    {
        $this->checkSession();

        $rules = [
            'name'   => 'required|string|max:100',
            'email'  => 'required|email|unique:users,email,' . $account->id,
            'role'   => 'required|in:admin,hotel,client',
            'status' => 'required|in:active,inactive',
        ];

        $messages = [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique'   => 'Este correo ya está registrado por otra cuenta.',
            'role.required'  => 'El rol es obligatorio.',
            'status.required'=> 'El estado es obligatorio.',
        ];

        // Contraseña opcional en edición
        if ($request->filled('password')) {
            $rules['password'] = 'min:6|confirmed';
            $messages['password.min']       = 'La contraseña debe tener al menos 6 caracteres.';
            $messages['password.confirmed'] = 'Las contraseñas no coinciden.';
        }

        $request->validate($rules, $messages);

        $data = [
            'name'   => $request->name,
            'email'  => $request->email,
            'role'   => $request->role,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $account->update($data);

        return redirect()->route('accounts.index')->with('success', 'Cuenta actualizada correctamente.');
    }

    public function destroy(User $account)
    {
        $this->checkSession();

        // Evitar que el admin se elimine a sí mismo
        if ($account->id === session('user_id')) {
            return redirect()->route('accounts.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'Cuenta eliminada correctamente.');
    }
}
