<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $committees = $user->committees;
        $selectedCommitteeId = $request->input('committee_id');

        $query = Task::where('is_active', true)->with(['committee', 'submissions' => function($q) use ($user) {
            $q->where('user_id', $user->id);
        }]);

        if ($selectedCommitteeId) {
            $query->where('committee_id', $selectedCommitteeId);
        } else {
            $query->whereIn('committee_id', $committees->pluck('id'));
        }

        $tasks = $query->latest()->paginate(10);

        return view('user.tasks.index', compact('tasks', 'committees', 'selectedCommitteeId'));
    }

    public function show(Task $task)
    {
        $user = Auth::user();
        
        if (!$user->committees->contains($task->committee_id)) {
            abort(403, 'You do not have access to this task.');
        }

        $submission = TaskSubmission::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->first();

        return view('user.tasks.show', compact('task', 'submission'));
    }

    public function submit(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $user = Auth::user();

        if (!$user->committees->contains($task->committee_id)) {
            abort(403, 'You do not have access to this task.');
        }

        $path = $request->file('file')->store('submissions', 'public');

        TaskSubmission::updateOrCreate(
            ['user_id' => $user->id, 'task_id' => $task->id],
            ['file' => $path, 'status' => 'pending']
        );

        return redirect()->back()->with('success', 'Task submitted successfully!');
    }
}
