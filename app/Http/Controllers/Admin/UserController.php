<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\ModelsOld\Cards as OldCards;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function search(Request $request)
    {
        // Arama sorgusu
        $search = $request->input('search');

        // Kullanıcıları veritabanından çekiyoruz ve filtreliyoruz
        $users = User::query()
            ->when($search, function ($query) use ($search) {
                // Arama sorgusu varsa, isme veya e-maile göre filtreleme yap
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'desc') // Son eklenen kayıtları önce göster
            ->paginate(10); // 10'lu sayfalama

        // $users = User::with('posts')
        //     ->when($search, function($query) use ($search) {
        //         $query->where('name', 'LIKE', "%{$search}%")
        //               ->orWhere('email', 'LIKE', "%{$search}%");
        //     })
        //     ->orderBy('id', 'desc')
        //     ->paginate(10);

        // Admin view'ine kullanıcıları ve arama sorgusunu gönderiyoruz
        // return view('admin.users.normal-users.list', compact('users', 'search'));
        return view('admin.users.normal-users.list', ['users' => $users, 'search' => $search]);
    }

    public function normal_users_list()
    {
        $getUsers = User::where('is_active', 1)->where('user_type', 1)->orderBy('id', 'desc')->paginate(10);
        return view('admin.users.normal-users.list', [
            'users' => $getUsers,
        ]);
    }

    public function business_users_list()
    {
        $getUsers = User::where('is_active', 1)->where('user_type', 2)->orderBy('id', 'desc')->paginate(10);
        return view('admin.users.business-users.list', [
            'users' => $getUsers,
        ]);
    }

    public function premium_users_list()
    {
        $getUsers = User::where('is_active', 1)->where('user_type', 3)->orderBy('id', 'desc')->paginate(10);
        return view('admin.users.premium-users.list', [
            'users' => $getUsers,
        ]);
    }

    public function unapproved_users_list()
    {
        $getUsers = User::where('is_active', 0)->orderBy('id', 'desc')->paginate(10);
        return view('admin.users.unapproved-users.list', [
            'users' => $getUsers,
        ]);
    }

    public function create_user()
    {
        return view('admin.users.create');
    }

    public function create_user_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile_image' => 'required|image',
            'phone' => 'required|numeric',
            'email' => 'required',
            'password' => 'required',
            'password_confirm' => 'required',
            'gender' => 'numeric',
            // 'date' => 'required',
            'job_desc' => 'required',
            'company_name' => 'required',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $user = new User();
            $user->name = $request->input('name');
            $user->phone = $request->input('phone');
            $user->email = $request->input('email');
            if ($request->input('password') == $request->input('password_confirm')) {
                $user->password = Hash::make($request->password);

                if ($request->hasFile('profile_image')) {
                    $file = $request->file('profile_image');
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/profile_image', $filename);
                    $user->image = 'storage/profile_image/' . $filename;
                }
                $user->gender = $request->input('gender');
                $user->birth_date = $request->input('date');
                $user->job_desc = $request->input('job_desc');
                $user->company_name = $request->input('company_name');
                $user->is_active = 0;
                $user->save();

                // Başarılı bir şekilde tamamlandıktan sonra yönlendirme yapabilirsiniz
                return redirect()->route('admin.users.unapproved.list')->with('success', 'Kullanıcı oluşturma işlemi başarılı.');
            } else {
                return redirect()->back()->with('errors', 'Girdiğiniz Şifreler uyuşmuyor!');
            }
        }
    }

    public function edit_user($id)
    {
        $user = User::find($id);

        // OldDB
        $oldCard = OldCards::where('user_id', $user->id)->first();

        $parentEmail = '';

        if (!empty($oldCard)) {
            $parent = $oldCard->parent_id;

            $parent = User::find($oldCard->parent_id);

            $parentEmail = '';

            if (!empty($parent)) {
                $parentEmail = $parent->email;
            }
        }

        return view('admin.users.edit', [
            'user' => $user,
            'oldCard' => $oldCard,
            'parent' => $parentEmail
        ]);
    }

    public function edit_user_post(Request $request)
    {
        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'profile_image' => 'nullable|image',
            'phone' => 'required|numeric',
            'email' => 'required',
            'password' => 'nullable',
            'password_confirm' => 'nullable',
            'gender' => 'numeric',
            // 'date' => 'required',
            'job_desc' => 'nullable',
            'company_name' => 'nullable',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {
            $user = User::find($request->input('user_id'));

            if ($user != null) {
                $user->name = $request->input('name');
                $user->phone = $request->input('phone');
                $user->email = $request->input('email');

                // Old database update
                $oldCard = OldCards::where('user_id', $user->id)->first();

                if (!empty($oldCard)) {
                    if (!empty($request->input('parent_id'))) {
                        $parent = User::where('email', $request->input('parent_id'))->first();

                        $oldCard->parent_id = $parent->id;
                    }

                    $oldCard->slug = $request->input('slug');
                    $oldCard->save();
                }

                $user->save();

                if ($request->input('password') == $request->input('password_confirm')) {
                    $user->password = Hash::make($request->password);

                    if ($request->hasFile('profile_image')) {
                        $file = $request->file('profile_image');
                        $filename = uniqid() . '_' . $file->getClientOriginalName();
                        $file->storeAs('public/profile_image', $filename);
                        $user->image = 'storage/profile_image/' . $filename;
                    }
                    $user->gender = $request->input('gender');
                    $user->birth_date = $request->input('date');
                    $user->job_desc = $request->input('job_desc');
                    $user->company_name = $request->input('company_name');
                    $user->save();

                    // Başarılı bir şekilde tamamlandıktan sonra yönlendirme yapabilirsiniz
                    return redirect()->back()->with('success', 'Kullanıcı güncelleme işlemi başarılı.');
                } else {
                    return redirect()->back()->with('errors', 'Girdiğiniz Şifreler uyuşmuyor!');
                }
            } else {
                return redirect()->back()->with('errors', 'Kullanıcı bulunamadı!');
            }
        }
    }

    public function approveUser($id)
    {

        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->withError('Kullanıcı bulunamadı.');
        } else {
            $user->is_active = 1;
            $user->save();

            return redirect()->back()->withSuccess('Kullanıcı onaylandı.');
        }
    }


    public function userDelete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('Kullanıcı bulunamadı!');
        } else {
            $user->delete();
            return redirect()->back()->with('Kullanıcı başarıyla silindi.');
        }
    }

    public function forgotPassword(Request $request)
    {
        $user = User::find($request->input('id'));

        if ($user) {
            $hash = $this->sifre_sifirla_hash($user->email);

            $oldCard = OldCards::where('user_id', $user->id)->first();

            try {
                $this->sendMail($user->email, $hash, $oldCard->language);
            } catch (\Exception $e) {
                Log::error('Mail failed: ' . $e->getMessage());

                return "<div class='alert alert-danger'>Sistemde bir hata meydana geldi. Lütfen tekrar deneyin</div>";
            }

            DB::connection('oldmysql')->insert("insert into sifre_sifirla set kullanici_id = ?,email = ?,hash = ?,durum = ?", [$user->id, $user->email, $hash, 0]);

            return redirect()->back()->with('success', 'Sifreniz sıfırlandı. Mail adresinize sıfırlama linki aktarıldı.');
        } else {
            return redirect()->back()->with('errors', 'Kullanıcı bulunamadı!');
        }
    }

    /**
     * Sifremi unuttum model
     */
    public function sifre_sifirla_hash($email)
    {
        return md5(hash("sha256", md5($email . time())));
    }

    public function sendMail($recipient, $hash, $userLanguage)
    {
        $title = 'Diji-Card parola sıfırlama iletisi.';

        if ($userLanguage == 'tr')
            $title =  'Diji-Card parola sıfırlama iletisi.';

        if ($userLanguage == 'en')
            $title = 'DijiCard password reset message.';

        if ($userLanguage == 'ar')
            $title =  'رسالة إعادة تعيين كلمة مرور DijiCard.';

        // Data to be passed to the email view
        $data = [
            'title' => $title,
            'hash' => $hash,
            'userLanguage' => $userLanguage
        ];

        // Send the email
        Mail::to($recipient)->send(new PasswordReset($data));

        return true;
    }
}
