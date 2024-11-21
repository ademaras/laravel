<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CountryCode;
use App\Models\LinkCategories;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserLinkInfo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    public function index()
    {
        $countryCodes = CountryCode::all();
        $users = User::where('parent_id', Auth::user()->id)->get();

        return view('user.team.list', compact('users', 'countryCodes'));
    }

    public function personList()
    {
        $users = User::with('userBasic')->where('parent_id', Auth::user()->id)->get();

        $responseData = [
            'user' => $users
        ];

        return response()->json($responseData);
    }

    public function upload()
    {
        return view('user.team.upload');
    }

    /**
     * Show the form for editing the specified resource.
     * And we check if user has the permission to edit this user
     */
    public function edit($id)
    {
        $personal = User::where('id', $id)->where('parent_id', Auth::user()->id)->first();

        if (!$personal) {
            return redirect()->route('user.teams.index')->with('error', 'Böyle bir kullanıcı bulunamadı.');
        }

        return view('user.personal.edit', compact('personal'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'company_name' => 'required|string|max:255',
            'job' => 'required|string|max:255',
        ], [
            'name.required' => 'Ad soyad alanı zorunludur.',
            'name.max' => 'Ad soyad alanı en fazla :max karakter olmalıdır.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'phone.required' => 'Telefon alanı zorunludur.',
            'company_name.required' => 'Şirket adı alanı zorunludur.',
            'company_name.max' => 'Şirket adı alanı en fazla :max karakter olmalıdır.',
            'job.required' => 'Görev alanı zorunludur.',
            'job.max' => 'Görev alanı en fazla :max karakter olmalıdır.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator, 'personal_update_error')->withInput();
        }

        $personal = User::findOrFail($id);

        $personal->name = $request->name;
        $personal->email = $request->email;
        $personal->phone = $request->phone;
        $personal->save();

        $userBasic = $personal->userBasic;
        if ($userBasic) {
            $userBasic->company_name = $request->company_name;
            $userBasic->name = $request->name;
            $userBasic->job = $request->job;
            $userBasic->save();
        }

        if ($request->hasFile('profile_img')) {
            $image = $request->file('profile_img');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $imagename = 'storage/profiles/' . $image_name;

            $personal->userBasic()->update([
                'profile_img' => $imagename,
            ]);

            $personal->image = $imagename;
            $personal->save();
        }

        if ($request->password) {
            $personal->password = Hash::make($request->password);
            $personal->save();
        }

        return redirect()->route('user.teams.index')->with('success', 'Personal edit successfully');
    }

    public function destroy($id)
    {
        $get_personal = User::find($id);
        if ($get_personal) {
            $get_personal->delete();
        }
        return back();
    }

    public function active($id)
    {
        $get_personal = User::find($id);
        if ($get_personal) {
            $get_personal->is_active = !$get_personal->is_active;
            $get_personal->save();
        }
        return back();
    }

    public function link($id)
    {
        $person = User::where('id', $id)->where('parent_id', Auth::user()->id)->first();

        if (!$person) {
            return redirect()->route('user.teams.index')->with('error', 'Böyle bir kullanıcı bulunamadı.');
        }

        $linkCategories = LinkCategories::all();

        $userLinks = UserLinkInfo::where('user_id', $id)->orderBy('order', 'asc')->get();

        $contactData = UserLinkInfo::where('link_input_id', 27)->first();
        
        return view('user.team.link', [

            'linkCategories' => $linkCategories,
            'userLinks' => $userLinks,
            'person' => $person,
            'contactData' => $contactData,
            'user_id' => $id,
        ]);
    }

    public function staticts()
    {
        return view('user.analytic.index');
    }

    private function paginate($items, $perPage)
    {
        $currentPage = request()->input('page') ?: 1;
        $pagedData = $items->slice(($currentPage - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator($pagedData, count($items), $perPage);
        $paginator->setPath(request()->url());

        return $paginator;
    }

    public function network()
    {
        $contacts = UserContact::where('user_id', Auth::id())->get();

        $digicards = $contacts->map(function ($contact) {
            $userInfo = User::where('phone', $contact->phone)->first();

            $otherContacts = $userInfo ? UserContact::where('user_id', $userInfo->id)->get() : null;

            $user = $contact->user;

            return [
                'contact' => $contact,
                'inDijicard' => $user !== null,
                'dijicard' => $user,
                'otherContacts' => $otherContacts,
            ];
        });

        $digicards = $this->paginate($digicards, 10);

        return view('user.team.network', compact('digicards'));
    }

    public function map()
    {
        return view('user.team.map');
    }

    public function getPersonalLatLng(Request $request)
    {
        $selectedPersonnel = $request->input('selectedPersonnel');
        $userId = Auth::user()->id;
        $firebaseDatabaseURL = 'https://dijicard-e8dfc-default-rtdb.firebaseio.com/';
        $path = '/users/' . $userId . '.json';
        $apiKey = 'AIzaSyBK7fHIxwK3rpxPIhJP3GjrFuXMG8A-WEI';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $firebaseDatabaseURL . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['stuffs'][$selectedPersonnel])) {
            $personalData = $data['stuffs'][$selectedPersonnel];

            $user = User::find($selectedPersonnel);
            $name = $user->name;

            return response()->json([
                'name' => $name,
                'lat' => $personalData['lat'],
                'lon' => $personalData['lon'],
            ]);
        } else {
            return response()->json(['error' => 'Personel bulunamadı'], 404);
        }
    }

    public function mapShift()
    {
        return view('user.team.map-shift');
    }

    public function bulkDelete(Request $request)
    {
        $userIds = $request->input('user_ids');

        User::whereIn('id', $userIds)->delete();

        return redirect()->back()->with('success', 'Seçilen kullanıcılar başarıyla silindi.');
    }

    public function bulkActive(Request $request)
    {
        $userIds = $request->input('user_ids');

        User::whereIn('id', $userIds)->update(['is_active' => 1]);

        return redirect()->back()->with('success', 'Seçilen kullanıcılar başarıyla aktif edildi.');
    }

    public function bulkPassive(Request $request)
    {
        $userIds = $request->input('user_ids');

        User::whereIn('id', $userIds)->update(['is_active' => 0]);

        return redirect()->back()->with('success', 'Seçilen kullanıcılar başarıyla pasif edildi.');
    }
}
