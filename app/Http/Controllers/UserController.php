<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth; 

class UserController extends Controller
{

    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para gestionar usuarios.');
        }

        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para crear usuarios.');
        }

        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para crear usuarios.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,editor'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'activo' => $request->has('activo') ? $request->boolean('activo') : true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para editar usuarios.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para editar usuarios.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,editor'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        // Actualizar la visibilidad/estado de la cuenta solo si viene en el formulario.
        if ($request->has('activo')) {
            $nuevoEstado = $request->boolean('activo');

            if (! $nuevoEstado && $this->esUltimoAdminActivo($user)) {
                return back()->withInput()
                    ->with('error', 'No puedes desactivar al último administrador activo.');
            }

            $user->update(['activo' => $nuevoEstado]);
        }

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para eliminar usuarios.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    public function promoteToAdmin(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para cambiar roles.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes cambiar tu propio rol.');
        }

        $user->update(['role' => 'admin']);
        return redirect()->route('admin.users.index')
            ->with('success', 'Editor promovido a administrador.');
    }


    public function demoteToEditor(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para cambiar roles.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes degradar tu propio usuario.');
        }

        if ($this->esUltimoAdminActivo($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes degradar al último administrador activo.');
        }

        $user->update(['role' => 'editor']);
        return redirect()->route('admin.users.index')
            ->with('success', 'Administrador degradado a editor.');
    }

    /**
     * Alterna el estado activo/inactivo de un usuario.
     */
    public function toggleActivo(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para cambiar el estado de usuarios.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes desactivar tu propio usuario.');
        }

        if ($user->estaActivo() && $this->esUltimoAdminActivo($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes desactivar al último administrador activo.');
        }

        $user->update(['activo' => ! $user->estaActivo()]);

        $mensaje = $user->estaActivo()
            ? 'Usuario activado correctamente.'
            : 'Usuario desactivado. No podrá iniciar sesión hasta que lo reactives.';

        return redirect()->route('admin.users.index')->with('success', $mensaje);
    }

    /**
     * ¿Es el único administrador que sigue activo?
     */
    private function esUltimoAdminActivo(User $user): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        return User::query()
            ->where('role', 'admin')
            ->where('activo', true)
            ->whereKeyNot($user->getKey())
            ->doesntExist();
    }
}