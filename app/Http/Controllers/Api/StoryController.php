<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserStories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StoryController extends Controller
{
    public function index()
    {
        $user =  Auth::user();
        $stories = UserStories::where('user_id', '!=', $user->id)->recentStories()->with('user')->get();
        return $this->mobile(true, 'Hikayeler', $stories);
    }

    public function me()
    {
        $user =  Auth::user();
        $stories = UserStories::where('user_id', $user->id)->recentStories()->with('user')->get();
        return $this->mobile(true, 'Hikayeler', $stories);
    }

    public function past()
    {
        $user =  Auth::user();
        $stories = UserStories::where('user_id', $user->id)->pastStories()->with('user')->get();
        return $this->mobile(true, 'Hikayeler', $stories);
    }

    public function show($id)
    {
        $stories = UserStories::with('user')->recentStories()->find($id);
        return $this->mobile(true, 'Hikayeler', $stories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,gif',
        ], [
            'file.required' => 'Dosya yüklemesi zorunludur.',
            'file.file' => 'Lütfen bir dosya yükleyin.',
            'file.mimes' => 'Dosya formatı yalnızca JPEG, PNG veya GIF olmalıdır.'
        ]);

        if ($validator->fails()) {
            return $this->mobile(false, $validator->errors()->first());
        }

        $user = Auth::user();

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $image_name = Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/story', $image_name);
            $image_path = 'storage/story/' . $image_name;
        } else {
            return $this->mobile(false, 'Dosya yüklenemedi');
        }
        $mime_type = $request->file('file')->getMimeType();

        $story = new UserStories();
        $story->user_id = $user->id;
        $story->file = $image_path;
        $story->mime_type = $mime_type;
        $story->save();

        return $this->mobile(true, 'Hikaye oluşturuldu', $story);
    }

    public function destroy($id)
    {
        $stories = UserStories::find($id);
        if (!$stories) {
            return $this->mobile(true, 'Hikaye bulunamadı');
        }
        $stories->delete();
        return $this->mobile(true, 'Hikaye silindi');
    }
}
