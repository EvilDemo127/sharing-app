<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;
use App\Models\QuestionComment;
use Inertia\Inertia;

class QuestionSaveController extends Controller
{
    public function question_save()
    {
        $userId = Auth::id();
        // $questions =Question::where('user_id',Auth::id())->get();
        $questions = Question::whereHas('qsave', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['comment.user', 'user', 'comment'])->withCount('like', 'comment', 'qsave')->withExists(['like as is_Like' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->withExists(['qsave as is_Save' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->latest()->get();
        return Inertia::render('Question', ['questions' => $questions]);
    }



    public function save_handle($id)
    {
        $userId = Auth::id();
        $quesSave = Question::find($id);
        $isSave = $quesSave->qsave()->where('user_id', $userId)->first();
        if ($isSave) {
            $isSave->delete();
            $save = false;
        } else {
            $quesSave->qsave()->create([
                'user_id' => $userId
            ]);
            $save = true;
        }
        $saveCount = $quesSave->qsave()->count();
        return response()->json([
            'is_Save' => $save,
            'qsave_count' => $saveCount
        ]);
    }
}
