<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionComment;
use Illuminate\Support\Facades\Auth;

class QuestionCommentController extends Controller
{
    public function comment_create(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = QuestionComment::create([
            'user_id' => Auth::id(),
            'question_id' => $id,
            'comment' => $request->comment
        ]);
        $commentCount = QuestionComment::where('question_id', $id)->count();
        $comment->load('user');
        return response()->json([
            'comment' => $comment,
            'comment_count' => $commentCount
        ]);
    }

    public function edit_comment($id)
    {
        $comment = QuestionComment::find($id);

        $comment->comment = request()->comment;
        $comment->save();
        return response()->json(['success' => 'Update Comment']);
    }

    public function delete_comment($id)
    {
        $comment = QuestionComment::find($id);
        $comment->delete();
        return response()->json(['success' => 'Success delete']);
    }
}
