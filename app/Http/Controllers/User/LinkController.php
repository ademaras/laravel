<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Links;
use App\Models\UserLinkInfo;
use Illuminate\Support\Facades\Auth;

class LinkController extends Controller
{
    public function saveInputs(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);
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

        return redirect()->back()->with('link_success', "Başarılı");
    }

    public function updateInputs(Request $request, $id)
    {
        $user = Auth::user();

        $linkInfo = UserLinkInfo::where('id', $id)
            ->where('user_id', $user->id)
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

    public function saveContact(Request $request, $id)
    {
        $user = User::find(Auth::user()->id);
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
        $user = Auth::user();

        $linkInfo = UserLinkInfo::where('id', $id)
            ->where('user_id', $user->id)
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

    public function contactEdit(Request $request, $id)
    {
        $user = Auth::user();
        $userLink = $user->userLinks()->find($id);

        if (!$userLink) {
            return $this->mobile(false, 'Bağlantı bulunamadı');
        }

        $validator = Validator::make($request->all(), [
            'link_input_id' => 'exists:link_inputs,id',
            'title' => 'required',
            'content' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = time() . uniqid() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/icons', $image_name);
            $imagename = 'storage/icons/' . $image_name;
        }

        $contactContent = $request->input('contact_content', $userLink->contact_content);

        $userLink->update([
            'title' => $request->title,
            'image' => $imagename ?? null,
            'link_input_id' => $request->link_input_id ?? $userLink->link_input_id,
            'content' => $request->content ?? $userLink->content,
            'contact_content' => json_encode($contactContent),
        ]);

        return $this->mobile(true, 'Bağlantı güncellendi', $userLink);
    }

    public function deleteInputs($id)
    {
        $linkInfo = UserLinkInfo::find($id);

        if (!$linkInfo) {
            return $this->response(false, 'Bağlantı kurulamadı.');
        }

        $linkInfo->delete();

        return back();
    }

    public function showStatus($id)
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
            'status' => $userLinkInfo->status ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
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
