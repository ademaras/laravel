<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserContact;
use App\Models\User;
use App\Models\UserStories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function list()
    {
        $contacts = UserContact::where('user_id', Auth::id())->get();
        $data = [];

        foreach ($contacts as $key => $value) {
            $data[$key] = $value;

            $check = User::where('phone', $value->phone)->first();

            if ($check) {
                $data[$key]['inDijicard'] = true;
                $data[$key]['dijicard'] = $check;
            } else {
                $data[$key]['inDijicard'] = false;
                $data[$key]['dijicard'] = null;
            }
        }

        return $this->mobile(true, 'Kişiler', $data);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'title' => 'nullable',
            'company' => 'nullable',
            'web' => 'nullable',
            'instagram' => 'nullable',
            'note' => 'nullable',
            'lat' => 'nullable',
            'lon' => 'nullable',
            'image' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $contact = new UserContact();
        $contact->user_id = Auth::id();
        $contact->card_id = Auth::user()->personalCard->id;
        $contact->name = $request->name;
        $contact->phone = $request->phone;
        $contact->email = $request->email;
        $contact->title = $request->title;
        $contact->company = $request->company;
        $contact->web = $request->web;
        $contact->instagram = $request->instagram;
        $contact->note = $request->note;
        $contact->lat = $request->lat;
        $contact->lon = $request->lon;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/user_contact', $image_name);
            $imagename = 'storage/user_contact/' . $image_name;
            $contact->image = $imagename;
        }

        $contact->save();

        return $this->mobile(true, 'Kişi eklendi', $contact);
    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'title' => 'nullable',
            'company' => 'nullable',
            'web' => 'nullable',
            'instagram' => 'nullable',
            'note' => 'nullable',
            'image' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $contact = UserContact::find($id);
        $contact->name = $request->name;
        $contact->phone = $request->phone;
        $contact->email = $request->email;
        $contact->title = $request->title;
        $contact->company = $request->company;
        $contact->web = $request->web;
        $contact->instagram = $request->instagram;
        $contact->note = $request->note;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/user_contact', $image_name);
            $imagename = 'storage/user_contact/' . $image_name;
            $contact->image = $imagename;
        }

        $contact->save();

        return $this->mobile(true, 'Kişi düzenlendi', $contact);
    }

    public function contactAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'title' => 'nullable',
            'company' => 'nullable',
            'web' => 'nullable',
            'instagram' => 'nullable',
            'note' => 'nullable',
            'lat' => 'nullable',
            'lon' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $contact = new UserContact();
        $contact->user_id = Auth::id();
        $contact->card_id = Auth::user()->personalCard->id;
        $contact->name = $request->name;
        $contact->phone = $request->phone;
        $contact->email = $request->email;
        $contact->title = $request->title;
        $contact->company = $request->company;
        $contact->web = $request->web;
        $contact->instagram = $request->instagram;
        $contact->note = $request->note;
        $contact->lat = $request->lat;
        $contact->lon = $request->lon;
        $contact->save();

        return $this->mobile(true, 'Kişi eklendi', $contact);
    }

    public function delete($id)
    {
        $contact = UserContact::find($id);
        $contact->delete();

        return $this->mobile(true, 'Kişi silindi');
    }
}
