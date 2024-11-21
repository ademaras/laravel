<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CorporateLinkInfo;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFormInput;
use App\Models\UserLinkInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userLinks = UserLinkInfo::where('user_id', $user->id)->with('link')->orderBy('order', 'asc')->get();
        $corporateLinks = CorporateLinkInfo::where('user_id', $user->id)->with('link')->orderBy('order', 'asc')->get();
        return view('user.dashboard.contact', compact('userLinks', 'corporateLinks'));
    }

    public function statusInput($id)
    {
        $user = User::find(Auth::user()->id);

        UserFormInput::find($id)->update([
            'required' => !$user->userFormInputs->where('id', $id)->first()->required
        ]);

        return $this->response(true, $user->userFormInputs->where('id', $id)->first()->required);
    }

    public function formUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|max:255',
            'declaration' => 'nullable|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response(false, null, $validator->errors());
        }

        $user = User::find(Auth::user()->id);

        if (!$user->userForm()->exists()) {
            $user->userForm()->create([
                'card_id' => $user->personalCard->id,
                'user_id' => $user->id,
            ]);
        }

        $user->userForm->update([
            'title' => $request->title,
            'declaration' => $request->declaration,
        ]);

        return back();
    }

    public function formStatus()
    {
        $user = User::find(Auth::user()->id);

        $user->userForm->update([
            'show' => !$user->userForm->show
        ]);

        return $this->response(true, $user->userForm->show);
    }
}
