<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Comments;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comments::where('business_id', Auth::user()->id)->get();
        return view('user.team.comments', compact('comments'));
    }

    public function delete($id)
    {
        $comments = Comments::find($id);
        $comments->delete();
        return redirect()->back()->with('success', 'Yorum silindi');
    }
}
