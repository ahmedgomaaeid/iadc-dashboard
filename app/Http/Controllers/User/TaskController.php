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
            'file' => 'nullable|file|max:10240', // 10MB max
            'text_content' => 'nullable|string',
        ]);

        // Ensure at least one submission type is provided
        if (!$request->hasFile('file') && !$request->filled('text_content')) {
            return back()->withErrors(['submission' => 'Please provide either a file or text content.']);
        }

        $user = Auth::user();

        if (!$user->committees->contains($task->committee_id)) {
            abort(403, 'You do not have access to this task.');
        }

        if ($task->deadline && $task->deadline->isPast()) {
            return back()->withErrors(['submission' => 'The deadline for this task has passed.']);
        }

        // Find or create the submission
        $submission = TaskSubmission::firstOrNew([
            'user_id' => $user->id,
            'task_id' => $task->id
        ]);

        // Handle file upload
        if ($request->has('uploaded_file')) {
            $tempPath = $request->input('uploaded_file');
            
            if (Storage::disk('local')->exists($tempPath)) {
                $fileName = str_replace('temp_uploads/', '', $tempPath);
                $parts = explode('_', $fileName, 2);
                $originalName = count($parts) > 1 ? $parts[1] : $fileName;
                
                // Delete old file if it exists
                if ($submission->file && Storage::disk('public')->exists($submission->file)) {
                    Storage::disk('public')->delete($submission->file);
                }
                
                // Move to public storage
                $newPath = 'submissions/' . $fileName;
                Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                Storage::disk('local')->delete($tempPath);
                
                $submission->file = $newPath;
            }
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            
            if (!$file->isValid()) {
                return back()->withErrors(['submission' => 'File upload failed. Please try again.']);
            }
            
            // Delete old file if it exists
            if ($submission->file && Storage::disk('public')->exists($submission->file)) {
                Storage::disk('public')->delete($submission->file);
            }
            
            // Store new file
            $path = $request->file('file')->store('submissions', 'public');
            $submission->file = $path;
        }

        // Update text content
        if ($request->has('text_content')) {
            $submission->text_content = $request->input('text_content');
        }
        
        // Reset status to pending when resubmitting
        $submission->status = 'pending';
        
        $submission->save();

        return redirect()->back()->with('success', 'Task submitted successfully!');
    }
}
