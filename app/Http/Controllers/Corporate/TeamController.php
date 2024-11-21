<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\UserDepartment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TeamController extends Controller
{
    public function list()
    {
        $users = User::where('parent_id', Auth::user()->id)->get();
        return view('user.team.list', compact('users'));
    }

    public function upload()
    {
        return view('user.team.upload');
    }

    public function role()
    {
        $all = Role::all();
        return view('user.team.role', compact('all'));
    }

    public function roleCreate()
    {
        return view('user.role.create');
    }

    public function roleCreatePost(Request $request)
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

        return $this->response(true, 'Role created successfully');
    }

    public function roleEdit($id)
    {
        $role = Role::find($id);
        $permissions = Permission::all();
        return view('user.role.edit', compact('role', 'permissions'));
    }

    public function roleEditPost(Request $request, $id)
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

        return $this->response(true, 'Role updated successfully');
    }

    public function roleUsers($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return redirect()->back();
        }

        $users = User::where('parent_id', Auth::user()->id)->role($role->name)->get();
        $subusers = User::where('parent_id', Auth::user()->id)->get();
        return view('user.role.users', compact('role', 'users', 'subusers'));
    }

    public function roleDelete($id)
    {
        $role = Role::find($id);
        $role->delete();
        return $this->response(true, 'Role deleted successfully');
    }

    public function department()
    {
        $all = Department::all();
        return view('user.team.department', compact('all'));
    }

    public function departmentCreate()
    {
        return view('user.department.create');
    }

    public function departmentCreatePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $department = Department::create([
            'name' => $request->name,
            'user_id' => Auth::user()->id
        ]);

        return $this->response(true, 'Department created successfully');
    }

    public function departmentEdit($id)
    {
        $department = Department::find($id);
        return view('user.department.edit', compact('department'));
    }

    public function departmentEditPost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $department = Department::find($id);
        $department->name = $request->name;
        $department->save();

        return $this->response(true, 'Department updated successfully');
    }

    public function departmentDelete($id)
    {
        $department = Department::find($id);
        $department->delete();
        return $this->response(true, 'Department deleted successfully');
    }

    public function departmentUsers($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return redirect()->back();
        }

        $all = UserDepartment::where('department_id', $id)->get()->pluck('user_id')->toArray();
        $users = User::whereIn('id', $all)->get();

        return view('user.department.users', compact('department', 'users'));
    }

    public function departmentRemove($user, $department)
    {
        $userDepartment = UserDepartment::where('user_id', $user)->where('department_id', $department)->first();
        $userDepartment->delete();
        return redirect()->back()->with('success', 'User removed from department successfully');
    }

    public function comments()
    {
        return view('user.team.comments');
    }
}
