<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('pedidos')
            ->withSum('pedidos', 'total')
            ->orderBy('created_at', 'desc');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtro por fecha de registro
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $usuarios = $query->paginate(20)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'users' => User::where('role', 'user')->count(),
            'con_pedidos' => User::has('pedidos')->count(),
            'sin_pedidos' => User::doesntHave('pedidos')->count(),
        ];

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function show(User $usuario)
    {
        $usuario->loadCount('pedidos');
        $usuario->loadSum('pedidos', 'total');
        $usuario->load(['pedidos' => function($query) {
            $query->with(['items.producto', 'items.tienda'])
                  ->orderBy('created_at', 'desc')
                  ->limit(50);
        }]);

        return Inertia::render('Admin/Usuarios/Show', [
            'usuario' => $usuario,
        ]);
    }

    public function edit(User $usuario)
    {
        $usuario->loadCount('pedidos');
        $usuario->loadSum('pedidos', 'total');

        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'role' => 'required|in:user,admin',
            'password' => 'nullable|confirmed|min:8',
        ]);

        // Guardar valores anteriores antes de actualizar
        $rolAnterior = $usuario->role;

        // Remover password_confirmation si existe
        if (isset($validated['password_confirmation'])) {
            unset($validated['password_confirmation']);
        }

        // Solo actualizar la contraseña si se proporcionó
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        // Registrar actividad
        $cambios = [];
        if ($request->filled('role') && $request->role !== $rolAnterior) {
            $cambios[] = "rol cambiado a {$validated['role']}";
        }
        if ($request->filled('password')) {
            $cambios[] = "contraseña actualizada";
        }
        
        $descripcionCambios = !empty($cambios) ? ' (' . implode(', ', $cambios) . ')' : '';
        
        ActivityLog::log(
            'actualizar_usuario',
            "Usuario actualizado: {$usuario->name}{$descripcionCambios}",
            '✏️',
            'yellow',
            $usuario
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $usuario)
    {
        $nombre = $usuario->name;
        $usuario->delete();

        // Registrar actividad
        ActivityLog::log(
            'eliminar_usuario',
            "Usuario eliminado: {$nombre}",
            '🗑️',
            'red'
        );

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado correctamente');
    }
}
