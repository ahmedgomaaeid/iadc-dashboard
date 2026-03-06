<?php

namespace App\Http\Controllers\Board;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\WhatsAppService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{
    /**
     * Display a listing of the lessons.
     */
    public function index()
    {
        $board = Auth::guard('board')->user();
        
        $lessons = Lesson::where('committee_id', $board->committee_id)
            ->withCount('attachments')
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('board.lessons.index', compact('lessons'));
    }

    /**
     * Show the form for creating a new lesson.
     */
    public function create()
    {
        $board = Auth::guard('board')->user();
        
        return view('board.lessons.form', compact('board'));
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(Request $request)
    {
        $board = Auth::guard('board')->user();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'youtube_url' => 'nullable|string|url',
            'attachments.*' => 'nullable|file|max:10240', // 10MB each
            'is_active' => 'boolean',
        ]);

        // Extract links from content
        $tags = Lesson::extractLinks($request->content ?? '');

        // Extract Video ID from URL
        $youtubeVideoId = null;
        if ($request->youtube_url) {
            $youtubeVideoId = Lesson::extractVideoId($request->youtube_url);
        }

        // Create lesson
        $lesson = Lesson::create([
            'board_id' => $board->id,
            'committee_id' => $board->committee_id,
            'title' => $validated['title'],
            'content' => $request->content,
            'youtube_video_id' => $youtubeVideoId,
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lessons/attachments', 'public');
                
                LessonAttachment::create([
                    'lesson_id' => $lesson->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        try {
            // get users in committee from committee_user table
            $members = $committee->users()->where('is_active', true)->get();
            $message = 'مساء الخير \n حابين نبلغك إن في درس جديدة اتضاف، ادخل على الـ Dashboard وشوف التفاصيل. \n 🔗 ' . route('lessons.show', $lesson->id);
            foreach ($members as $member) {
                WhatsAppService::send($member->phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('Error sending lesson notification: ' . $e->getMessage());
        }

        return redirect()->route('board.lessons.index')
            ->with('success', 'Lesson created successfully!');
    }

    /**
     * Display the specified lesson.
     */
    public function show(Lesson $lesson)
    {
        // Ensure board can only view lessons from their committee
        $board = Auth::guard('board')->user();
        
        if ($lesson->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized action.');
        }

        $lesson->load('attachments');

        return view('board.lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified lesson.
     */
    public function edit(Lesson $lesson)
    {
        // Ensure board can only edit their own lessons
        $board = Auth::guard('board')->user();
        
        if ($lesson->board_id !== $board->id) {
            abort(403, 'Unauthorized action.');
        }

        $lesson->load('attachments');

        return view('board.lessons.form', compact('board', 'lesson'));
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        // Ensure board can only update their own lessons
        $board = Auth::guard('board')->user();
        
        if ($lesson->board_id !== $board->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'youtube_url' => 'nullable|string|url',
            'attachments.*' => 'nullable|file|max:10240', // 10MB each
            'is_active' => 'boolean',
            'remove_video' => 'boolean',
        ]);

        // Extract links from content
        $tags = Lesson::extractLinks($request->content ?? '');

        // Handle Video URL
        if ($request->youtube_url) {
            $lesson->youtube_video_id = Lesson::extractVideoId($request->youtube_url);
        } elseif ($request->remove_video) {
            $lesson->youtube_video_id = null;
        }

        // Update lesson
        $lesson->update([
            'title' => $validated['title'],
            'content' => $request->content,
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('lessons/attachments', 'public');
                
                LessonAttachment::create([
                    'lesson_id' => $lesson->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('board.lessons.index')
            ->with('success', 'Lesson updated successfully!');
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(Lesson $lesson)
    {
        // Ensure board can only delete their own lessons
        $board = Auth::guard('board')->user();
        
        if ($lesson->board_id !== $board->id) {
            abort(403, 'Unauthorized action.');
        }

        // YouTube videos don't need file deletion, just clear the ID

        // Delete attachments
        foreach ($lesson->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }

        // Delete lesson
        $lesson->delete();

        return redirect()->route('board.lessons.index')
            ->with('success', 'Lesson deleted successfully!');
    }

    /**
     * Delete a specific attachment.
     */
    public function deleteAttachment(LessonAttachment $attachment)
    {
        $board = Auth::guard('board')->user();
        
        // Ensure the attachment belongs to a lesson created by this board
        if ($attachment->lesson->board_id !== $board->id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);
        
        // Delete record
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully!');
    }
}
