<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\DepartmentUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmanController extends Controller
{
    public function list()
    {
        $all = Department::where('user_id', Auth::id())->with('users')->get();

        return $this->mobile(true, 'Bölümler', $all);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $departman = new Department();
        $departman->user_id = Auth::id();
        $departman->name = $request->name;
        $departman->save();

        return $this->mobile(true, 'Departman başarıyla oluşturuldu', $departman);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $departman = Department::find($id);
        $departman->name = $request->name;
        $departman->save();

        return $this->mobile(true, 'Departman başarıyla güncellendi', $departman);
    }

    public function delete($id)
    {
        $departman = Department::find($id);
        if (!$departman) {
            return $this->mobile(false, 'Departman bulunamadı');
        }
        $departman->delete();

        return $this->mobile(true, 'Departman başarıyla silindi');
    }

    public function addUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $departman = Department::find($id);

        if (!$departman) {
            return $this->mobile(false, 'Departman bulunamadı');
        }

        $departmanUser = new DepartmentUser();
        $departmanUser->department_id = $departman->id;
        $departmanUser->user_id = $request->user_id;
        $departmanUser->save();

        return $this->mobile(true, 'Kullanıcı departmana başarıyla eklendi');
    }

    public function removeUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $departman = Department::find($id);

        if (!$departman) {
            return $this->mobile(false, 'Departman bulunamadı');
        }

        $departmanUser = DepartmentUser::where('department_id', $departman->id)->where('user_id', $request->user_id)->first();

        if (!$departmanUser) {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }

        $departmanUser->delete();

        return $this->mobile(true, 'Kullanıcı departmandan başarıyla kaldırıldı');
    }
}
