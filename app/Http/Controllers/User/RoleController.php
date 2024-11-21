<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $all = Role::all();
        return view('user.team.role', compact('all'));
    }

    public function create()
    {
        return view('user.role.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $role = Role::create(['name' => $request->name]);
        $role->givePermissionTo($request->permissions);

        return back();
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permissions = Permission::all();
        return view('user.role.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $role = Role::find($id);
        $role->name = $request->name;
        $role->save();
        $role->syncPermissions($request->permissions);

        return redirect()->route('user.roles.index');
    }

    public function roleUsers($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return redirect()->back();
        }

        $users = User::where('parent_id', Auth::user()->id)->role($role->name)->get();
        $subusers = User::where('parent_id', Auth::user()->id)->get();

        $existingUserIds =  User::where('parent_id', Auth::user()->id)->role($role->name)->get()->pluck('id')->toArray();

        return view('user.role.users', compact('role', 'users', 'subusers', 'existingUserIds'));
    }

    public function roleUserCreate2(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $roleId = $request->input('role_id');

        $role = Role::findOrFail($roleId);

        $user->roles()->sync([$role->id]);

        return redirect()->back()->with('success', 'Kullanıcıya rol atandı.');
    }

    public function roleUserCreate(Request $request)
    {
        $selectedUserIds = $request->input('user_id');

        $roleId = $request->input('role_id');

        foreach ($selectedUserIds as $userId) {
            $user = User::findOrFail($userId);

            $role = Role::findOrFail($roleId);

            $user->roles()->attach($role->id);
        }

        return redirect()->back()->with('success', 'Seçilen kullanıcılara rol atandı.');
    }

    public function roleUserRemove(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $roleId = $request->input('role_id');

        $user->roles()->detach($roleId);

        return redirect()->back()->with('success', 'Kullanıcının rolü kaldırıldı.');
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        $role->delete();
        return redirect()->route('user.roles.index');
    }
}
