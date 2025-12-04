<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle attachments
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
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle new attachments
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
}
