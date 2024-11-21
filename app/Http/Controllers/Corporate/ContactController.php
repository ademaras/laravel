<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CorporateFormInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        return view('user.dashboard.contact');
    }

    public function statusInput($id)
    {
        $user = User::find(Auth::user()->id);

        CorporateFormInput::find($id)->update([
            'required' => !$user->corporateFormInputs->where('id', $id)->first()->required
        ]);

        return $this->response(true, $user->corporateFormInputs->where('id', $id)->first()->required);
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

        if (!$user->corporateForm()->exists()) {
            $user->corporateForm()->create([
                'card_id' => $user->personalCard->id,
                'user_id' => $user->id,
            ]);
        }

        $user->corporateForm->update([
            'title' => $request->title,
            'declaration' => $request->declaration,
        ]);

        return $this->response(true, 'Form ayarları güncellendi.');
    }

    public function formStatus()
    {
        $user = User::find(Auth::user()->id);

        $user->corporateForm->update([
            'show' => !$user->corporateForm->show
        ]);

        return $this->response(true, $user->corporateForm->show);
    }
}
