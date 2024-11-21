<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\BusinessRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class AuthorityController extends Controller
{
    public function index()
    {
        $all = BusinessRole::where('user_id', Auth::user()->id)->with('role')->get();
        foreach ($all as $key => $value) {
            $all[$key]['role']['users'] = User::role($all[$key]['role']['name'])->get();
        }
        return $this->mobile(true, 'Roller', $all);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        if (Role::where('name', $request->name)->exists()) {
            return $this->mobile(false, 'Rol adı zaten mevcut');
        }

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        $business_role = BusinessRole::create([
            'user_id' => Auth::user()->id,
            'role_id' => $role->id,
        ]);

        $role->givePermissionTo($request->permissions);

        if ($request->users) {
            foreach ($request->users as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->assignRole($role->name);
                }
            }
        }

        return $this->mobile(true, 'Rol başarıyla oluşturuldu', $business_role);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $role = Role::find($id);

        if (!$role) {
            return $this->mobile(false, 'Rol bulunamadı');
        }

        $role->name = $request->name;
        $role->guard_name = 'web';
        $role->save();

        $role->syncPermissions($request->permissions);

        if ($request->users) {
            $role->users()->detach();
            foreach ($request->users as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->assignRole($role->name);
                }
            }
        }

        return $this->mobile(true, 'Rol başarıyla güncellendi');
    }

    public function listPermissions()
    {
        $permissions = Permission::all();

        return $this->mobile(true, 'İzinler başarıyla listelendi', $permissions);
    }
}
