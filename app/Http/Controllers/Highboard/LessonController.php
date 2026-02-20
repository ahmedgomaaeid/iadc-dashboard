<?php

namespace App\Http\Controllers\Highboard;

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
     * Display a listing of the resource.
     */
    public function index()
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Get lessons for committees in the highboard member's field
        $lessons = Lesson::whereHas('committee', function($query) use ($highboard) {
                $query->where('field_id', $highboard->field_id);
            })
            ->with(['committee', 'board', 'highboard'])
            ->withCount('attachments')
            ->latest()
            ->paginate(10);

        return view('highboard.lessons.index', compact('lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $highboard = Auth::guard('highboard')->user();
        $committees = $highboard->field->committees()->active()->get();
        
        return view('highboard.lessons.form', compact('committees'));
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
            'youtube_url' => 'nullable|url',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ]);

        $highboard = Auth::guard('highboard')->user();

        // Verify committee belongs to highboard's field
        $committee = $highboard->field->committees()->findOrFail($request->committee_id);

        // Extract Video ID
        $youtubeVideoId = null;
        if ($request->youtube_url) {
            $youtubeVideoId = Lesson::extractVideoId($request->youtube_url);
        }

        // Auto-detect links in content for tags
        $tags = $request->content ? Lesson::extractLinks($request->content) : [];

        // Create lesson
        $lesson = Lesson::create([
            'highboard_id' => $highboard->id,
            'committee_id' => $committee->id,
            'title' => $validated['title'],
            'content' => $request->content,
            'youtube_video_id' => $youtubeVideoId,
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = $file->getClientOriginalName();
                $path = $file->store('lesson_attachments', 'public');

                LessonAttachment::create([
                    'lesson_id' => $lesson->id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        try {
            $members = User::where('committee_id', $committee->id)->get();
            $message = 'تم إضافة درس جديد الي قسم ' . $committee->name . ' ( ' . $lesson->title . ' ) ' . route('lessons.show', $lesson->id);
            foreach ($members as $member) {
                WhatsAppService::send($member->phone, $message);
            }
        } catch (\Exception $e) {
            Log::error('Error sending lesson notification: ' . $e->getMessage());
        }

        return redirect()->route('highboard.lessons.index')
            ->with('success', 'Lesson created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        // Ensure highboard member can only view lessons from their field
        $highboard = Auth::guard('highboard')->user();
        if ($lesson->committee->field_id !== $highboard->field_id) {
            abort(403);
        }

        $lesson->load('attachments');
        return view('highboard.lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own lesson OR if it's a board lesson in the same field
        $isOwnLesson = $lesson->highboard_id === $highboard->id;
        $isBoardLessonInField = $lesson->board_id && $lesson->committee->field_id === $highboard->field_id;

        if (!$isOwnLesson && !$isBoardLessonInField) {
            abort(403);
        }

        $committees = $highboard->field->committees()->active()->get();
        return view('highboard.lessons.form', compact('lesson', 'committees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own lesson OR if it's a board lesson in the same field
        $isOwnLesson = $lesson->highboard_id === $highboard->id;
        $isBoardLessonInField = $lesson->board_id && $lesson->committee->field_id === $highboard->field_id;

        if (!$isOwnLesson && !$isBoardLessonInField) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'committee_id' => 'required|exists:committees,id',
            'content' => 'nullable|string',
            'youtube_url' => 'nullable|url',
            'attachments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,rar',
        ]);

        // Verify committee belongs to highboard's field
        $committee = $highboard->field->committees()->findOrFail($request->committee_id);

        // Extract Video ID
        $youtubeVideoId = $lesson->youtube_video_id;
        if ($request->has('remove_video')) {
            $youtubeVideoId = null;
        } elseif ($request->youtube_url) {
            $youtubeVideoId = Lesson::extractVideoId($request->youtube_url);
        }

        // Auto-detect links in content for tags
        $tags = $request->content ? Lesson::extractLinks($request->content) : [];

        $lesson->update([
            'committee_id' => $committee->id,
            'title' => $validated['title'],
            'content' => $request->content,
            'youtube_video_id' => $youtubeVideoId,
            'tags' => $tags,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // Handle new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = $file->getClientOriginalName();
                $path = $file->store('lesson_attachments', 'public');

                LessonAttachment::create([
                    'lesson_id' => $lesson->id,
                    'file_name' => $fileName,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('highboard.lessons.index')
            ->with('success', 'Lesson updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own lesson OR if it's a board lesson in the same field
        $isOwnLesson = $lesson->highboard_id === $highboard->id;
        $isBoardLessonInField = $lesson->board_id && $lesson->committee->field_id === $highboard->field_id;

        if (!$isOwnLesson && !$isBoardLessonInField) {
            abort(403);
        }

        // Delete attachments from storage
        foreach ($lesson->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $lesson->delete();

        return redirect()->route('highboard.lessons.index')
            ->with('success', 'Lesson deleted successfully.');
    }

    /**
     * Remove the specified attachment.
     */
    public function destroyAttachment(LessonAttachment $attachment)
    {
        $lesson = $attachment->lesson;
        $highboard = Auth::guard('highboard')->user();
        
        // Allow if own lesson OR if it's a board lesson in the same field
        $isOwnLesson = $lesson->highboard_id === $highboard->id;
        $isBoardLessonInField = $lesson->board_id && $lesson->committee->field_id === $highboard->field_id;

        if (!$isOwnLesson && !$isBoardLessonInField) {
            abort(403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);
        
        // Delete record
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
