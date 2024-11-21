<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Directory;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\NewDijicarUserNotification;
use App\Services\NotificationToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required',
            'password' => 'required'
        ], [
            'login.required' => 'Kullanıcı adı zorunludur.',
            'password.required' => 'Şifre zorunludur.'
        ]);


        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $login = $request->login;

        $user = User::where('email', $login)->orWhere('username', $login)->orWhere('phone', $login)->first();

        if ($user) {
            if (Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
                if (!$user->personalCard()->exists()) {
                    $user->personalCard()->create([
                        'user_id' => $user->id,
                    ]);
                }
                $user->inBasic = false;

                if (!$user->userBasic()->exists()) {
                    $user->inBasic = true;
                }

                $user->token = $user->createToken('auth_token')->plainTextToken;
                return $this->mobile(true, 'Giriş başarılı', $user);
            } else {
                return $this->mobile(false, 'Şifre yanlış');
            }
        } else {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }
    }

    public function loginPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ], [
            'phone.required' => 'Telefon numarası zorunludur.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $phone = $request->phone;

        $user = User::where('phone', $phone)->first();

        if ($user) {

            $verificationData = $this->sendVerificationCode($user->id);

            $data = [
                'user_id' => $user->id,
                'verification_data' => $verificationData,
            ];

            return $this->mobile(true, 'Doğrulama kodu gönderildi', $data);
        } else {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|unique:users,phone',
                'password' => 'required|confirmed'
            ],
            [
                'name.required' => 'Ad soyad alanı zorunlu',
                'email.required' => 'Email alanı zorunlu',
                'email.email' => 'Geçerli bir email adresi girin',
                'email.unique' => 'Bu email adresi zaten kullanılıyor',
                'phone.unique' => 'Bu telefon numarası zaten kullanılıyor',
                'password.required' => 'Şifre alanı zorunlu',
                'password.confirmed' => 'Şifre tekrarı eşleşmiyor'
            ]
        );

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = new User();
        $user->name = $request->name;

        $username = strtolower(str_replace([' ', 'ç', 'ğ', 'ı', 'i', 'ö', 'ş', 'ü'], ['', 'c', 'g', 'i', 'i', 'o', 's', 'u'], $request->name));

        $count = User::where('username', $username)->count();
        if ($count > 0) {
            $username = $username . ($count + 1);
        }

        $user->username = $username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->is_active = 0;
        $user->save();

        $user->token = $user->createToken('auth_token')->plainTextToken;

        // control

        $token = null;
        $title = 'Yeni Dijicar Kullanıcısı';
        $message = 'Rehberinizdeki ' . $user->name . ' katıldı.';

        $notificationToken = new NotificationToken();
        $notification = new NewDijicarUserNotification();
        $tokens = $notificationToken->notificationToken($token);
        $newUserPhones = $user->phone;

        $directory = Directory::where('phone', $newUserPhones)->get();

        foreach ($directory as $entry) {
            $existingUser = User::find($entry->user_id);

            if ($existingUser) {
                $userToken = $tokens[$existingUser->id] ?? null;

                if ($userToken) {
                    $notification->send($userToken, $title, $message);
                }
            }
        }

        // control

        return $this->mobile(true, 'Kayıt başarılı', $user);
    }

    public function forgotpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email'
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.exists' => 'Girilen e-posta adresi sistemimizde bulunmamaktadır.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $code = rand(100000, 999999);
            $user->verificationCode()->create([
                'user_id' => $user->id,
                'code' => $code
            ]);

            $data = [
                'name' => $user->name,
                'code' => $code
            ];

            // Mail::send('emails.forgotpassword', $data, function($message) use ($user) {
            //     $message->to($user->email, $user->name)->subject('Forgot Password');
            // });

            return $this->mobile(true, 'Doğrulama kodu e-postanıza gönderildi');
        } else {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }
    }

    public function resetpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|exists:verification_codes,code',
            'password' => 'required|confirmed'
        ], [
            'code.required' => 'Doğrulama kodu zorunludur.',
            'code.exists' => 'Girilen doğrulama kodu geçerli değil.',
            'password.required' => 'Şifre zorunludur.',
            'password.confirmed' => 'Şifreler uyuşmuyor.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $verificationCode = VerificationCode::where('code', $request->code)->first();

        if ($verificationCode) {
            $user = User::where('id', $verificationCode->user_id)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            $verificationCode->delete();

            return $this->mobile(true, 'Şifre sıfırlama başarılı');
        } else {
            return $this->mobile(false, 'Doğrulama kodu bulunamadı');
        }
    }

    public function loginVerifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'code' => 'required|digits:4',
        ], [
            'user_id.required' => 'Kullanıcı kimliği zorunludur.',
            'user_id.exists' => 'Girilen kullanıcı kimliği sistemimizde bulunmamaktadır.',
            'code.required' => 'Doğrulama kodu zorunludur.',
            'code.digits' => 'Doğrulama kodu 4 haneli olmalıdır.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }

        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('updated_at', '>=', now())
            ->first();

        if (!$verificationCode) {
            return $this->mobile(false, 'Geçersiz veya süresi dolmuş doğrulama kodu');
        }

        Auth::login($user);

        if (!$user->personalCard()->exists()) {
            $user->personalCard()->create([
                'user_id' => $user->id,
            ]);
        }
        $user->inBasic = false;

        if (!$user->userBasic()->exists()) {
            $user->inBasic = true;
        }

        $user->token = $user->createToken('auth_token')->plainTextToken;

        $verificationCode->delete();
        return $this->mobile(true, 'Doğrulama başarılı', $user);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'code' => 'required|digits:6',
        ], [
            'user_id.required' => 'Kullanıcı kimliği zorunludur.',
            'user_id.exists' => 'Girilen kullanıcı kimliği sistemimizde bulunmamaktadır.',
            'code.required' => 'Doğrulama kodu zorunludur.',
            'code.digits' => 'Doğrulama kodu 6 haneli olmalıdır.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return $this->mobile(false, 'Kullanıcı bulunamadı');
        }

        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('updated_at', '>=', now())
            ->first();

        if (!$verificationCode) {
            return $this->mobile(false, 'Geçersiz veya süresi dolmuş doğrulama kodu');
        }

        $user->update(['is_active' => true]);

        $user->token = $user->createToken('auth_token')->plainTextToken;
        $verificationCode->delete();
        return $this->mobile(true, 'Doğrulama başarılı', $user);
    }

    protected function sendVerificationCode($userId)
    {
        VerificationCode::where('user_id', $userId)->delete();

        $verificationCode = new VerificationCode();
        $verificationCode->user_id = $userId;
        $verificationCode->code = rand(1000, 9999);
        $verificationCode->updated_at = now()->addMinutes(10);
        $verificationCode->save();

        return [
            'message' => 'Doğrulama kodu gönderildi',
            'code' => $verificationCode->code,
        ];
    }
}
