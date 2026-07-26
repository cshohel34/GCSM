<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]);
        return view('settings.roles', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $perms = $request->input('permissions', []);
        $role->syncPermissions($perms);
        return back()->with('status', $role->name.' permissions updated.');
    }
}
