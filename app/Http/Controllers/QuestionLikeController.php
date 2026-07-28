<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

class QuestionLikeController extends Controller
{
    public function like_handle($id)
    {
        $user = Auth::id();
        $questionLike = Question::findOrFail($id);

        $isLike = $questionLike->like()->toggle($user);
        $like = count($isLike['attached']) > 0;
        $questionLike->loadCount('like');
        $likeCount = $questionLike->like_count;
        return response()->json([
            'is_like' => $like,
            'like_count' => $likeCount
        ]);
    }
}
