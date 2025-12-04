<?php

namespace App\Http\Controllers\Board;

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
        $board = Auth::guard('board')->user();
        
        // Get tasks for the board member's committee
        $tasks = Task::where('committee_id', $board->committee_id)
            ->withCount('attachments')
            ->latest()
            ->paginate(10);

        return view('board.tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('board.tasks.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ]);

        $board = Auth::guard('board')->user();

        // Auto-detect links in content for tags
        $tags = $request->content ? Task::extractLinks($request->content) : [];

        // Create task
        $task = Task::create([
            'board_id' => $board->id,
            'committee_id' => $board->committee_id,
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

        return redirect()->route('board.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Ensure board member can only view tasks from their committee
        $board = Auth::guard('board')->user();
        if ($task->committee_id !== $board->committee_id) {
            abort(403);
        }

        $task->load('attachments');
        return view('board.tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        // Ensure board member can only edit their own tasks
        $board = Auth::guard('board')->user();
        if ($task->board_id !== $board->id) {
            abort(403);
        }

        return view('board.tasks.form', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        // Ensure board member can only update their own tasks
        $board = Auth::guard('board')->user();
        if ($task->board_id !== $board->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ]);

        // Auto-detect links in content for tags
        $tags = $request->content ? Task::extractLinks($request->content) : [];

        $task->update([
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

        return redirect()->route('board.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        // Ensure board member can only delete their own tasks
        $board = Auth::guard('board')->user();
        if ($task->board_id !== $board->id) {
            abort(403);
        }

        // Delete attachments from storage
        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $task->delete();

        return redirect()->route('board.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Remove the specified attachment.
     */
    public function destroyAttachment(TaskAttachment $attachment)
    {
        $task = $attachment->task;
        
        // Ensure board member can only delete attachments from their own tasks
        $board = Auth::guard('board')->user();
        if ($task->board_id !== $board->id) {
            abort(403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);
        
        // Delete record
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
