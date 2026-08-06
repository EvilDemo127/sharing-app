<?php

namespace App\Http\Controllers;

use App\Events\QuestionLike;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\User;
use App\Notifications\LikeNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class QuestionLikeController extends Controller
{
    public function like_handle($id)
    {
        $user = Auth::id();
        $questionLike = Question::findOrFail($id);

        $isLike = $questionLike->like()->toggle($user);
        $like = count($isLike['attached']) > 0;

        if ($like) {
            if ($questionLike->user_id !== $user) {
                $is_like = DatabaseNotification::where('notifiable_id', $questionLike->user_id)->where('notifiable_type', User::class)->where('type', LikeNotification::class)->where('data->actor_id', $user)->where('data->target_type', 'question')->where('data->target_id', $questionLike->id)->exists();
                if (!$is_like) {
                    $questionLike->user->notify(new LikeNotification(Auth::user(), $questionLike));
                }
            }
        }

        $questionLike->loadCount('like');
        $likeCount = $questionLike->like_count;
        logger('QuestionLike dispatch');

        // event(new QuestionLike($questionLike));
        broadcast(new QuestionLike($questionLike))->toOthers();
        return response()->json([
            'is_like' => $like,
            'like_count' => $likeCount
        ]);
    }
}
