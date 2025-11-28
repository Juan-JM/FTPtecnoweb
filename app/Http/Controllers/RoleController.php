<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class RoleController extends Controller
{
    public function assignRoleToUser()
    {
        $user = User::find(1); // Cambia 1 por el ID del usuario que quieras
        $user->assignRole('admin'); // Asigna el rol 'admin'
        return response()->json(['message' => 'Rol asignado con éxito']);
    }

    public function updateUserRole(Request $request)
    {
        $user = User::find($request->user_id);
        $user->assignRole($request->role);
        return back()->with('success', 'Rol asignado con éxito.');
    }
}
