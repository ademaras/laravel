<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLinkInfo;
use App\Models\Notification;
use App\Models\UserStories;
use App\Services\NewDijicarUserNotification;
use App\Services\NotificationToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function details()
    {
        $user = Auth::user();
        return $this->mobile(true, 'Kullanıcı detayları', $user);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|unique:users,phone,' . $user->id,
            'birth_date' => 'nullable|date',
        ], [
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.unique' => 'Bu kullanıcı adı başka bir kullanıcı tarafından kullanılıyor.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.',
            'phone.unique' => 'Bu telefon numarası başka bir kullanıcı tarafından kullanılıyor.',
            'birth_date.date' => 'Geçerli bir doğum tarihi girin.',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        if ($request->file('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $image = 'storage/profiles/' . $image_name;
            $user->image = $image; // Eğer kullanıcı profil resmini güncelliyorsanız.
        }

        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->birth_date = Carbon::parse($request->birth_date)->format('Y-m-d');
        $user->save();

        return $this->mobile(true, 'Kullanıcı güncellendi', $user);
    }

    public function usernameUpdate(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username,' . $user->id,
        ], [
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.unique' => 'Bu kullanıcı adı başka bir kullanıcı tarafından kullanılıyor.',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false);
        }

        $user->username = $request->username;
        $user->save();

        return $this->mobile(true);
    }

    public function delete()
    {
        $user = Auth::user();
        $user->delete();

        return $this->mobile(true, 'Kullanıcı silindi');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed',
        ], [
            'old_password.required' => 'Mevcut şifre zorunludur.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Yeni şifreler birbiriyle uyuşmuyor.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = Auth::user();
        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();

            return $this->mobile(true, 'şifre değişti');
        } else {
            return $this->mobile(false, 'Eski şifre yanlış');
        }
    }

    public function notifyToggle()
    {
        $user = Auth::user();
        $user->notification = !$user->notification;
        $user->save();

        return $this->mobile(true, 'Bildirim değiştirildi', $user);
    }

    public function notifications()
    {
        $all = Notification::where('user_id', Auth::id())->orWhere('user_id', null)->orderBy('id', 'desc')->get();
        return $this->mobile(true, 'Bildirimler', $all);
    }

    public function userDetail($id)
    {
        $user = User::where('id', $id)->with('userBasic', 'userLinks', 'pools', 'catalogs')->first();
        return $this->mobile(true, 'Kullanıcı detayları', $user);
    }

    public function updateLink(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable',
            'image' => 'nullable',
            'content' => 'required',
        ], [
            'title.nullable' => 'Başlık alanı boş bırakılabilir.',
            'image.nullable' => 'Resim alanı boş bırakılabilir.',
            'content.required' => 'İçerik alanı zorunludur.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user_link = UserLinkInfo::where('user_id', Auth::user()->id)->where('id', $id)->first();
        if (!$user_link) {
            return $this->mobile(false, 'Bulunamadı');
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/socials', $image_name);
            $imagename = 'storage/socials/' . $image_name;
            $user_link->image = $imagename;
        }

        $user_link->title = $request->title;
        $user_link->content = $request->content;
        $user_link->save();
        return $this->mobile(true, 'Bağlantı Güncellendi', $user_link);
    }

    public function levels()
    {
        $user = Auth::user();

        $level = new \stdClass();
        $level->level = 1;
        $level->name = 'Orta';

        $user->level = $level;

        return $this->mobile(true, 'Kullanıcı düzeyi', $user);
    }

    public function newUserNotification(Request $request)
    {
        $token = null;

        $title = 'Yeni Dijicar Kullanıcısı';
        $message = 'Rehberinizdeki bir kişi Dijicar\'a katıldı.';

        $newUserPhones = $request->input('phones', []);

        $notificationToken = new NotificationToken();
        $notification = new NewDijicarUserNotification();
        $tokens = $notificationToken->notificationToken($token);

        foreach ($newUserPhones as $phone) {
            $existingUser = User::where('phone', $phone)->first();

            if (!$existingUser) {
                return $this->mobile(true, "Numara bulunamadı");
            }

            if ($existingUser) {
                $userToken = $tokens[$existingUser->id] ?? null;
                if ($userToken) {
                    $notificationResult = $notification->send($userToken, $title, $message);
                    if ($notificationResult['success']) {
                        return $this->mobile(true, 'Bildirim gönderildi ');
                    } else {
                        return $this->mobile(false, 'Bildirim gönderme hatası: ' . $notificationResult['results'][0]['error']);
                    }
                }
            }
        }
    }

    public function changePackage(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'user_type' => 'required|in:1,2,3',
        ], [
            'user_type.required' => 'Kullanıcı adı zorunludur.',
            'user_type.in' => '1 => ücretsiz kullanıcı, 2 => aktivasyonlu kullanıcı ,3 premium kullanıcı.',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }
        $user->user_type = $request->user_type;
        $user->save();
        return $this->mobile(true, 'Kullanıcının paketi başarıyla değiştirildi', $user);
    }
}
