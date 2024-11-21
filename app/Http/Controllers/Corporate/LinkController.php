<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Links;
use App\Models\CorporateLinkInfo;
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
            $userLinks = CorporateLinkInfo::where('user_id', $user->id)->get();
            foreach ($userLinks as $userLink) {
                $userLink->order += 1;
                $userLink->save();
            }

            CorporateLinkInfo::create([
                'card_id' => $user->personalCard->id,
                'user_id' => $user->id,
                'link_input_id' => $input->id,
                'content' => $request->{'text'},
                'sticky' => $request->sticky ?? 0,
            ]);
        }

        return redirect()->back()->with('link_success_corporate', "Başarılı");
    }

    public function updateInputs(Request $request, $id)
    {
        $user = Auth::user();

        $linkInfo = CorporateLinkInfo::where('id', $id)
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
            $userLinks = CorporateLinkInfo::where('user_id', $user->id)->get();
            foreach ($userLinks as $userLink) {
                $userLink->order += 1;
                $userLink->save();
            }

            CorporateLinkInfo::create([
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

        $linkInfo = CorporateLinkInfo::where('id', $id)
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

    public function deleteInputs($id)
    {
        $user = Auth::user();

        $linkInfo = CorporateLinkInfo::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$linkInfo) {
            return $this->response(false, 'Bağlantı kurulamadı.');
        }

        $linkInfo->delete();

        return back();
    }

    public function showStatus($id)
    {
        $user = User::find(Auth::user()->id);

        $link = CorporateLinkInfo::find($id);

        if (!$link) {
            return $this->response(false, 'Bağlantı bulunamadı.');
        }

        $CorporateLinkInfo = CorporateLinkInfo::where('user_id', $user->id)->where('id', $id)->first();

        if (!$CorporateLinkInfo) {
            return $this->response(false, 'Bağlantı kurulmamış.');
        }

        $CorporateLinkInfo->update([
            'status' => $CorporateLinkInfo->status ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
    }

    public function stickyStatus($id)
    {
        $user = User::find(Auth::user()->id);

        $link = CorporateLinkInfo::find($id);

        if (!$link) {
            return $this->response(false, 'Bağlantı bulunamadı.');
        }

        $CorporateLinkInfo = CorporateLinkInfo::where('user_id', $user->id)->where('id', $id)->first();

        if (!$CorporateLinkInfo) {
            return $this->response(false, 'Bağlantı kurulmamış.');
        }

        $CorporateLinkInfo->update([
            'sticky' => $CorporateLinkInfo->sticky ? 0 : 1,
        ]);

        return $this->response(true, $CorporateLinkInfo->sticky);
    }

    public function linkedForm()
    {
        $user = User::find(Auth::user()->id);

        $user->businessCard->update([
            'link_form' => $user->businessCard->link_form ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
    }

    public function singleLink()
    {
        $user = User::find(Auth::user()->id);

        $user->businessCard->update([
            'single_link' => $user->businessCard->single_link ? 0 : 1,
        ]);

        return $this->response(true, 'Durum güncellendi.');
    }
}
