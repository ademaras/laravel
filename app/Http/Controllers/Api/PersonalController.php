<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Models\Announcement;
use App\Models\DepartmentUser;
use App\Models\WorkingHour;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PersonalController extends Controller
{
    public function list()
    {
        $users = User::where('parent_id', Auth::id())->with(['contacts', 'workingHour'])->get()
            ->map(function ($user) {
                $user->setHidden([]);
                unset($user->email_verified_at, $user->remember_token);
                return $user;
            });

        return $this->mobile(true, 'Kullanıcılar', $users);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|mimes:jpeg,jpg,png',
            'name' => 'required',
            'title' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|min:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = new User();
        $user->parent_id = Auth::id();
        $user->name = $request->name;
        $user->job_desc = $request->title;
        $user->email = $request->email;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $imagename = 'storage/profiles/' . $image_name;
            $user->image = $imagename;
        }
        $user->password = Hash::make($request->password);
        $user->save();

        $workingHour = WorkingHour::create([
            'user_id' => $user->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        $user->working_hour = $workingHour;

        $user->setHidden([]);
        unset($user->email_verified_at, $user->remember_token);

        return $this->mobile(true, 'Kullanıcı eklendi', $user);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|mimes:jpeg,jpg,png',
            'name' => 'required',
            'title' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = User::where('id', $id)->where('parent_id', Auth::id())->first();

        if (!$user) {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }

        $user->name = $request->name;
        $user->job_desc = $request->title;
        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $imagename = 'storage/profiles/' . $image_name;
            $user->image = $imagename;
        }

        $user->save();

        $workingHour = WorkingHour::where('user_id', $user->id)->first();

        if (!$workingHour) {
            $workingHour = WorkingHour::create([
                'user_id' => $user->id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            $workingHour->save();
        } else {
            $workingHour->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
        }

        $user->working_hour = $workingHour;

        $user->setHidden([]);
        unset($user->email_verified_at, $user->remember_token);

        return $this->mobile(true, 'Kullanıcı güncellendi', $user);
    }

    public function delete($id)
    {
        $user = User::where('id', $id)->where('parent_id', Auth::id())->first();

        if (!$user) {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }

        $user->delete();

        return $this->mobile(true, 'Kullanıcı silindi');
    }

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'type' => 'required|in:1,2,3',
            'user_ids' => 'required_if:type,1|array',
            'department_ids' => 'required_if:type,2|array',
            'phones' => 'required_if:type,3|array',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        if ($request->type == 1) {
            $userIds = $request->user_ids;
            foreach ($userIds as $userId) {
                $notification = new Notification();
                $notification->user_id = Auth::user()->id;
                $notification->delivery_user = $userId;
                $notification->title = $request->title;
                $notification->description = $request->description;
                $notification->type = $request->type;
                $notification->save();
            }
        } elseif ($request->type == 2) {
            $departmentIds = $request->department_ids;
            foreach ($departmentIds as $departmentId) {
                $subUsers = DepartmentUser::where('department_id', $departmentId)->get();

                foreach ($subUsers as $user) {
                    $notification = new Notification();
                    $notification->user_id = Auth::user()->id;
                    $notification->delivery_user = $user->user_id;
                    $notification->title = $request->title;
                    $notification->description = $request->description;
                    $notification->type = $request->type;
                    $notification->save();
                }
            }
        } elseif ($request->type == 3) {
            $allPhones = $request->phones;
            foreach ($allPhones as $phone) {
                $user = User::where('phone', $phone)->first();

                if ($user) {
                    $notification = new Notification();
                    $notification->user_id = Auth::user()->id;
                    $notification->delivery_user = $user->id;
                    $notification->title = $request->title;
                    $notification->description = $request->description;
                    $notification->type = $request->type;
                    $notification->save();
                }
            }
        }

        return $this->mobile(true, 'Bildirim başarıyla gönderildi');
    }

    public function sendAnnouncement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'file' => 'nullable'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $subUsers = User::where('parent_id', Auth::id())->get();

        $file = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file_name = time() . uniqid() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/announcements', $file_name);
            $file = 'storage/announcements/' . $file_name;
        }

        foreach ($subUsers as $user) {
            $announcement = new Announcement();
            $announcement->sender_id = Auth::id();
            $announcement->receiver_id = $user->id;
            $announcement->title = $request->title;
            $announcement->content = $request->description;
            $announcement->file = $file;
            $announcement->save();
        }

        return $this->mobile(true, 'Duyuru başarıyla gönderildi');
    }

    public function activeToggle($id)
    {
        $user = User::where('id', $id)->where('parent_id', Auth::id())->first();

        if (!$user) {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return $this->mobile(true, 'Kullanıcı durumu güncellendi');
    }
}
