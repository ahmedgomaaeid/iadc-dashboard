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
            'uploaded_files' => 'nullable|array',
            'uploaded_files.*' => 'string',
            'text_content' => 'nullable|string',
        ]);

        // Ensure at least one submission type is provided
        if ((!$request->has('uploaded_files') || empty($request->input('uploaded_files'))) && !$request->filled('text_content')) {
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
        if ($request->has('uploaded_files') && !empty($request->input('uploaded_files'))) {
            // Delete old files if they exist
            if ($submission->files) {
                foreach ($submission->files as $oldFile) {
                    if (Storage::disk('public')->exists($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }
                }
            }

            $newFiles = [];
            foreach ($request->input('uploaded_files') as $tempPath) {
                if (Storage::disk('local')->exists($tempPath)) {
                    $fileName = str_replace('temp_uploads/', '', $tempPath);
                    // Try to restore original filename if possible, but ensure uniqueness or handling
                    // The temp file naming in ChunkUploadController is $filename . "_" . md5(time()) . "." . $extension;
                    // We can keep it or try to clean it. Let's keep existing logic preference if any.
                    // Existing logic: $parts = explode('_', $fileName, 2); $originalName = count($parts) > 1 ? $parts[1] : $fileName;
                    // But we need a unique name in submissions folder too.
                    // Let's us the temp filename directly as it's already uniqueified.
                    
                    $newPath = 'submissions/' . $fileName;
                    Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                    Storage::disk('local')->delete($tempPath);
                    
                    $newFiles[] = $newPath;
                }
            }
            
            $submission->files = $newFiles;
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
