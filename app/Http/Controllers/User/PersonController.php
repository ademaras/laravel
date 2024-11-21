<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CountryCode;
use App\Models\Links;
use App\Models\User;
use App\Models\UserLinkInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    public function index()
    {
        $users = User::where('parent_id', Auth::user()->id)->get();

        $formattedUsers = [];

        foreach ($users as $user) {
            $formattedUsers[] = [
                'id' => $user->id,
                'text' => $user->name,
            ];
        }

        return response()->json($formattedUsers);
    }

    public function step()
    {
        return view('user.personal.step');
    }

    public function step2()
    {
        $countryCodes = CountryCode::all();
        return view('user.personal.step2', compact('countryCodes'));
    }

    public function step3()
    {
        return view('user.personal.step3');
    }

    public function step4()
    {
        return view('user.personal.step4');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'job' => 'required',
            'company' => 'required',
            'image' => 'nullable',
            'country_code' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->parent_id = Auth::user()->id;
        $user->save();

        $user->personalCard()->create([
            'user_id' => $user->id,
        ]);

        $user = User::find($user->id);

        $user->name = $request->name;
        $user->country_code = $request->country_code;
        $user->phone = $request->phone;
        $user->save();

        if ($user->personalCard()->exists()) {
            if ($user->userBasic()->exists()) {
                $user->userBasic()->update([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            } else {
                $user->userBasic()->create([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            }
        } else {
            $user->personalCard()->create([
                'user_id' => $user->id,
            ]);

            if ($user->userBasic()->exists()) {
                $user->userBasic()->update([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            } else {
                $user->userBasic()->create([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            }
        }

        $user = User::find($user->id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $imagename = 'storage/profiles/' . $image_name;

            $user->userBasic()->update([
                'profile_img' => $imagename,
            ]);
            $user->image = $imagename;
            $user->save();
        }

        $user->userBasic()->update([
            'job' => $request->job,
            'company_name' => $request->company,
        ]);

        $user->job_desc = $request->job;
        $user->company_name = $request->company;
        $user->save();

        return redirect()->back()->with('success', 'Personel başarıyla oluşturuldu');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $newPersonId = $user->id;
        session(['new_person_id' => $newPersonId]);

        $user->personalCard()->create([
            'user_id' => $user->id,
        ]);

        return redirect()->route('user.teams.create2');
    }

    public function store2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newPersonId = session('new_person_id');

        // Kullanıcıyı bu kimlikle bulun
        $user = User::find($newPersonId);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        if ($user->personalCard()->exists()) {
            if ($user->userBasic()->exists()) {
                $user->userBasic()->update([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            } else {
                $user->userBasic()->create([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            }
        } else {
            $user->personalCard()->create([
                'user_id' => $user->id,
            ]);

            if ($user->userBasic()->exists()) {
                $user->userBasic()->update([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            } else {
                $user->userBasic()->create([
                    'card_id' => $user->personalCard->id,
                    'name' => $request->name,
                ]);
            }
        }

        return redirect()->route('user.teams.create3');
    }

    public function store3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job' => 'required',
            'company' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newPersonId = session('new_person_id');

        $user = User::find($newPersonId);

        $user->userBasic()->update([
            'job' => $request->job,
            'company_name' => $request->company,
        ]);

        $user->job_desc = $request->job;
        $user->company_name = $request->company_name;
        $user->save();

        return redirect()->route('user.teams.create4');
    }

    public function store4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newPersonId = session('new_person_id');

        $user = User::find($newPersonId);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profiles', $image_name);
            $imagename = 'storage/profiles/' . $image_name;

            $user->userBasic()->update([
                'profile_img' => $imagename,
            ]);
        }

        $user->image = $imagename;
        $user->parent_id = Auth::user()->id;
        $user->save();

        return redirect()->route('user.teams.index');
    }

    public function saveInputs(Request $request, $id, $user_id)
    {
        $user = User::find($user_id);
        $link = Links::find($id);

        if (!$link) {
            return $this->response(false, 'Bağlantı kurulamadı.');
        }

        if (!$user->personalCard()->exists()) {
            $user->personalCard()->create([
                'user_id' => $user->id,
            ]);
        }

        foreach ($link->inputs as $input) {
            // Mevcut kullanıcının bağlantılarını al ve order değerini 1 artır
            $userLinks = UserLinkInfo::where('user_id', $user->id)->get();
            foreach ($userLinks as $userLink) {
                $userLink->order += 1;
                $userLink->save();
            }

            UserLinkInfo::create([
                'card_id' => $user->personalCard->id,
                'user_id' => $user->id,
                'link_input_id' => $input->id,
                'content' => $request->{'text'},
                'sticky' => $request->sticky ?? 0,
            ]);
        }


        return back();
    }

    public function updateInputs(Request $request, $id,$user_id)
    {
        $linkInfo = UserLinkInfo::where('id', $id)
            ->where('user_id', $user_id)
            ->first();

        if (!$linkInfo) {
            return $this->response(false, 'Bağlantı kurulamadı.');
        }

        $content = $request->input('content');

        $linkInfo->update([
            'content' => $content,
        ]);

        return back();
    }

    public function showStatus($id,$user_id)
    {
        $user = User::find($user_id);

        $link = UserLinkInfo::find($id);

        if (!$link) {
            return $this->response(false, 'Bağlantı bulunamadı.');
        }

        $userLinkInfo = UserLinkInfo::where('user_id', $user->id)->where('id', $id)->first();

        if (!$userLinkInfo) {
            return $this->response(false, 'Bağlantı kurulmamış.');
        }

        $userLinkInfo->update([
            'status' => $userLinkInfo->status ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
    }

    public function saveContact(Request $request, $id)
    {
        $user = User::find($request->user_id);
        $link = Links::find($id);

        if (!$link) {
            return $this->response(false, 'Bağlantı kurulamadı.');
        }

        if (!$user->personalCard()->exists()) {
            $user->personalCard()->create([
                'user_id' => $user->id,
            ]);
        }

        $contactContent = $request->input('contact_content', []);

        foreach ($link->inputs as $input) {
            $userLinks = UserLinkInfo::where('user_id', $user->id)->get();
            foreach ($userLinks as $userLink) {
                $userLink->order += 1;
                $userLink->save();
            }

            UserLinkInfo::create([
                'card_id' => $user->personalCard->id,
                'user_id' => $user->id,
                'link_input_id' => $input->id,
                'content' => 0,
                'contact_content' => json_encode($contactContent),
            ]);
        }

        return back();
    }

    public function updateContact(Request $request, $id)
    {
        $user = $request->user_id;

        $linkInfo = UserLinkInfo::where('id', $id)
            ->where('user_id', $user)
            ->first();

        if (!$linkInfo) {
            return $this->response(false, 'Bağlantı kurulamadı.');
        }

        $content = 0;
        $contactContent = $request->input('contact_content', []);

        $linkInfo->update([
            'content' => $content,
            'contact_content' => json_encode($contactContent),
        ]);

        return back();
    }

    public function stickyStatus($id)
    {
        $user = User::find(Auth::user()->id);

        $link = UserLinkInfo::find($id);

        if (!$link) {
            return $this->response(false, 'Bağlantı bulunamadı.');
        }

        $userLinkInfo = UserLinkInfo::where('user_id', $user->id)->where('id', $id)->first();

        if (!$userLinkInfo) {
            return $this->response(false, 'Bağlantı kurulmamış.');
        }

        $userLinkInfo->update([
            'sticky' => $userLinkInfo->sticky ? 0 : 1,
        ]);

        return $this->response(true, $userLinkInfo->sticky);
    }

    public function linkedForm()
    {
        $user = User::find(Auth::user()->id);

        $user->personalCard->update([
            'link_form' => $user->personalCard->link_form ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
    }

    public function singleLink()
    {
        $user = User::find(Auth::user()->id);

        $user->personalCard->update([
            'single_link' => $user->personalCard->single_link ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
    }
}
