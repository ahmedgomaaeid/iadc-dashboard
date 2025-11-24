<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $committees = $user->committees;
        $selectedCommitteeId = $request->input('committee_id');

        $query = Lesson::active()->with('committee');

        if ($selectedCommitteeId) {
            $query->where('committee_id', $selectedCommitteeId);
        } else {
            $query->whereIn('committee_id', $committees->pluck('id'));
        }

        $lessons = $query->latest()->paginate(10);

        return view('user.lessons.index', compact('lessons', 'committees', 'selectedCommitteeId'));
    }

    public function show(Lesson $lesson)
    {
        $user = Auth::user();
        // Check if user has access to this lesson's committee
        if (!$user->committees->contains($lesson->committee_id)) {
            abort(403, 'You do not have access to this lesson.');
        }

        return view('user.lessons.show', compact('lesson'));
    }
}
