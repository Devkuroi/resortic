<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $accounts = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $stats = [
            'total'   => User::count(),
            'active'  => User::where('status', 'active')->count(),
            'hotels'  => User::where('role', 'hotel')->count(),
            'clients' => User::where('role', 'client')->count(),
        ];

        return view('accounts.index', compact('accounts', 'stats'));
    }

    public function create(): View
    {
        return view('accounts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:admin,hotel,client',
            'status'   => 'required|in:active,inactive',
        ], $this->validationMessages());

        User::create($request->only('name', 'email', 'password', 'role', 'status'));

        return redirect()->route('accounts.index')
            ->with('success', 'Cuenta creada exitosamente.');
    }

    public function edit(User $account): View
    {
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, User $account): RedirectResponse
    {
        $rules = [
            'name'   => 'required|string|max:100',
            'email'  => "required|email|unique:users,email,{$account->id}",
            'role'   => 'required|in:admin,hotel,client',
            'status' => 'required|in:active,inactive',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'min:6|confirmed';
        }

        $request->validate($rules, $this->validationMessages());

        $data = $request->only('name', 'email', 'role', 'status');

        if ($request->filled('password')) {
            $data['password'] = $request->password; // cast 'hashed' lo encripta
        }

        $account->update($data);

        return redirect()->route('accounts.index')
            ->with('success', 'Cuenta actualizada correctamente.');
    }

    public function destroy(User $account): RedirectResponse
    {
        if ($account->is(Auth::user())) {
            return redirect()->route('accounts.index')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Cuenta eliminada correctamente.');
    }

    /* ── Helpers privados ───────────────────────── */

    private function validationMessages(): array
    {
        return [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required'      => 'El rol es obligatorio.',
            'status.required'    => 'El estado es obligatorio.',
        ];
    }
}
