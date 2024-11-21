<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index()
    {
        $all = Department::all();
        return view('user.team.department', compact('all'));
    }

    public function create()
    {
        return view('user.department.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        } else {
            $department = Department::create([
                'name' => $request->department_name,
                'user_id' => Auth::user()->id
            ]);
        }
        return redirect()->route('user.department.index')->with('success', 'Department created successfully');
    }

    public function edit($id)
    {
        $department = Department::find($id);
        return view('user.department.edit', compact('department'));
    }

    public function update(Request $request, $id)
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

        return redirect()->route('user.department.index');
    }

    public function destroy($id)
    {
        $department = Department::find($id);

        if ($department) {
            $department->delete();
            return redirect()->route('user.department.index')->with('success', 'Department deleted');
        } else {
            return redirect()->back()->withErrors(['Department not deleted']);
        }
    }

    public function departmentUsers($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return redirect()->back();
        }

        $all = UserDepartment::where('department_id', $id)->get()->pluck('user_id')->toArray();
        $users = User::whereIn('id', $all)->get();

        $existingUserIds = $all;

        return view('user.department.users', compact('department', 'users', 'existingUserIds'));
    }

    public function departmentUserCreate(Request $request)
    {
        $selectedUserIds = $request->input('user_id');

        foreach ($selectedUserIds as $userId) {
            $userDepartman = new UserDepartment();
            $userDepartman->user_id = $userId;
            $userDepartman->department_id = $request->department_id;
            $userDepartman->save();
        }

        return back();
    }

    public function departmentRemove($user, $department)
    {
        $userDepartment = UserDepartment::where('user_id', $user)->where('department_id', $department)->first();
        $userDepartment->delete();
        return redirect()->back()->with('success', 'User removed from department successfully');
    }
}
