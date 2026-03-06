<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\WhatsAppService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Get tasks for committees in the highboard member's field
        $tasks = Task::whereHas('committee', function($query) use ($highboard) {
                $query->where('field_id', $highboard->field_id);
            })
            ->with(['committee', 'board', 'highboard'])
            ->withCount('attachments')
            ->latest()
            ->paginate(10);

        return view('highboard.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $highboard = Auth::guard('highboard')->user();
        $committees = $highboard->field->committees()->active()->get();
        
        return view('highboard.tasks.form', compact('committees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'committee_id' => 'required|exists:committees,id',
            'content' => 'nullable|string',
            'deadline' => 'nullable|date|after:now',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ]);

        $highboard = Auth::guard('highboard')->user();

        // Verify committee belongs to highboard's field
        $committee = $highboard->field->committees()->findOrFail($request->committee_id);

        // Auto-detect links in content for tags
        $tags = $request->content ? Task::extractLinks($request->content) : [];

        // Create task
        $task = Task::create([
            'highboard_id' => $highboard->id,
            'committee_id' => $committee->id,
            'title' => $validated['title'],
            'content' => $request->content,
            'deadline' => $validated['deadline'],
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle attachments
        if ($request->has('uploaded_files')) {
            foreach ($request->input('uploaded_files') as $tempPath) {
                // $tempPath is like "temp_uploads/identifier_filename.ext"
                if (Storage::disk('local')->exists($tempPath)) {
                    $fileName = str_replace('temp_uploads/', '', $tempPath);
                    // Remove identifier prefix if desired, or keep it to avoid conflicts
                    // Let's keep the original filename if possible, but for simplicity here use the temp name or extract
                    
                    // Extract original filename from "identifier_filename"
                    $parts = explode('_', $fileName, 2);
                    $originalName = count($parts) > 1 ? $parts[1] : $fileName;
                    
                    // Move to public storage
                    $newPath = 'task_attachments/' . $fileName;
                    Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                    Storage::disk('local')->delete($tempPath);

                    TaskAttachment::create([
                        'task_id' => $task->id,
                        'file_name' => $originalName,
                        'file_path' => $newPath,
                        'file_type' => Storage::disk('public')->mimeType($newPath),
                        'file_size' => Storage::disk('public')->size($newPath),
                    ]);
                }
            }
        }
        
        // Handle legacy attachments (fallback)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = $file->getClientOriginalName();
                $path = $file->store('task_attachments', 'public');

                TaskAttachment::create([
                    'task_id' => $task->id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        try {
            // get users in committee from committee_user table
            $members = $committee->users()->where('is_active', true)->get();
            $message = 'مساء الخير \n حابين نبلغك إن في تاسك جديدة اتضاف، ادخل على الـ Dashboard وشوف التفاصيل. \n 🔗 ' . route('tasks.show', $task->id);
            foreach ($members as $member) {
                WhatsAppService::send($member->phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('Error sending lesson notification: ' . $e->getMessage());
        }
        return redirect()->route('highboard.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Ensure highboard member can only view tasks from their field
        $highboard = Auth::guard('highboard')->user();
        if ($task->committee->field_id !== $highboard->field_id) {
            abort(403);
        }

        $task->load('attachments');
        return view('highboard.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own task OR if it's a board task in the same field
        $isOwnTask = $task->highboard_id === $highboard->id;
        $isBoardTaskInField = $task->board_id && $task->committee->field_id === $highboard->field_id;

        if (!$isOwnTask && !$isBoardTaskInField) {
            abort(403);
        }

        $committees = $highboard->field->committees()->active()->get();
        return view('highboard.tasks.form', compact('task', 'committees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own task OR if it's a board task in the same field
        $isOwnTask = $task->highboard_id === $highboard->id;
        $isBoardTaskInField = $task->board_id && $task->committee->field_id === $highboard->field_id;

        if (!$isOwnTask && !$isBoardTaskInField) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'committee_id' => 'required|exists:committees,id',
            'content' => 'nullable|string',
            'deadline' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ]);

        // Verify committee belongs to highboard's field
        $committee = $highboard->field->committees()->findOrFail($request->committee_id);

        // Auto-detect links in content for tags
        $tags = $request->content ? Task::extractLinks($request->content) : [];

        $task->update([
            'committee_id' => $committee->id,
            'title' => $validated['title'],
            'content' => $request->content,
            'deadline' => $validated['deadline'],
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle new attachments
        if ($request->has('uploaded_files')) {
            foreach ($request->input('uploaded_files') as $tempPath) {
                if (Storage::disk('local')->exists($tempPath)) {
                    $fileName = str_replace('temp_uploads/', '', $tempPath);
                    $parts = explode('_', $fileName, 2);
                    $originalName = count($parts) > 1 ? $parts[1] : $fileName;
                    
                    $newPath = 'task_attachments/' . $fileName;
                    Storage::disk('public')->put($newPath, Storage::disk('local')->get($tempPath));
                    Storage::disk('local')->delete($tempPath);

                    TaskAttachment::create([
                        'task_id' => $task->id,
                        'file_name' => $originalName,
                        'file_path' => $newPath,
                        'file_type' => Storage::disk('public')->mimeType($newPath),
                        'file_size' => Storage::disk('public')->size($newPath),
                    ]);
                }
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = $file->getClientOriginalName();
                $path = $file->store('task_attachments', 'public');

                TaskAttachment::create([
                    'task_id' => $task->id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('highboard.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own task OR if it's a board task in the same field
        $isOwnTask = $task->highboard_id === $highboard->id;
        $isBoardTaskInField = $task->board_id && $task->committee->field_id === $highboard->field_id;

        if (!$isOwnTask && !$isBoardTaskInField) {
            abort(403);
        }

        // Delete attachments from storage
        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $task->delete();

        return redirect()->route('highboard.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Remove the specified attachment.
     */
    public function destroyAttachment(TaskAttachment $attachment)
    {
        $task = $attachment->task;
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own task OR if it's a board task in the same field
        $isOwnTask = $task->highboard_id === $highboard->id;
        $isBoardTaskInField = $task->board_id && $task->committee->field_id === $highboard->field_id;

        if (!$isOwnTask && !$isBoardTaskInField) {
            abort(403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);
        
        // Delete record
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Display all submissions for the highboard member's field tasks
     */
    public function submissions(Request $request)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Get filters
        $taskId = $request->input('task_id');
        $committeeId = $request->input('committee_id');
        
        // Query submissions for tasks in the highboard's field
        $query = \App\Models\TaskSubmission::whereHas('task', function($q) use ($highboard) {
            $q->whereHas('committee', function($cq) use ($highboard) {
                $cq->where('field_id', $highboard->field_id);
            });
        })->with('user', 'task.committee');
        
        if ($taskId) {
            $query->where('task_id', $taskId);
        }
        
        if ($committeeId) {
            $query->whereHas('task', function($q) use ($committeeId) {
                $q->where('committee_id', $committeeId);
            });
        }
        
        $submissions = $query->latest()->paginate(15);
        
        // Get committees and tasks for filter dropdowns
        $committees = $highboard->field->committees()->active()->get();
        $tasks = Task::whereHas('committee', function($q) use ($highboard) {
            $q->where('field_id', $highboard->field_id);
        })->orderBy('title')->get();
        
        return view('highboard.tasks.submissions', compact('submissions', 'tasks', 'committees', 'taskId', 'committeeId'));
    }

    /**
     * Show the submission details
     */
    public function showSubmission(\App\Models\TaskSubmission $submission)
    {
        $highboard = Auth::guard('highboard')->user();

        // Verify the submission belongs to a task in the highboard's field
        if ($submission->task->committee->field_id !== $highboard->field_id) {
            abort(403);
        }

        return view('highboard.tasks.submission_show', compact('submission'));
    }

    /**
     * Accept a submission
     */
    public function acceptSubmission(Request $request, \App\Models\TaskSubmission $submission)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Verify the submission belongs to a task in the highboard's field
        if ($submission->task->committee->field_id !== $highboard->field_id) {
            abort(403);
        }
        
        $request->validate([
            'score' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // Check if already evaluated to avoid duplicates
        $existingEvaluation = \App\Models\UserEvaluation::where('related_type', get_class($submission))
            ->where('related_id', $submission->id)
            ->where('type', 'task_submission')
            ->first();
            
        if (!$existingEvaluation) {
            // Create evaluation
            \App\Models\UserEvaluation::create([
                'user_id' => $submission->user_id,
                'evaluator_type' => get_class($highboard),
                'evaluator_id' => $highboard->id,
                'committee_id' => $submission->task->committee_id,
                'type' => 'task_submission',
                'related_type' => get_class($submission),
                'related_id' => $submission->id,
                'score' => $request->score,
                'max_score' => 10,
                'evaluation_date' => now(),
                'event_name' => 'Task: ' . $submission->task->title,
            ]);
        }
        
        $submission->update([
            'status' => 'accepted',
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('highboard.tasks.submissions')->with('success', 'Submission accepted and evaluated successfully.');
    }

    /**
     * Reject a submission
     */
    public function rejectSubmission(\App\Models\TaskSubmission $submission)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Verify the submission belongs to a task in the highboard's field
        if ($submission->task->committee->field_id !== $highboard->field_id) {
            abort(403);
        }
        
        $submission->update(['status' => 'rejected']);
        
        return redirect()->route('highboard.tasks.submissions')->with('success', 'Submission rejected.');
    }
}
