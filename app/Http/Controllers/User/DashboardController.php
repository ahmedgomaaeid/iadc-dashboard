<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Task;
use App\Models\Quiz;
use App\Services\QuizCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $committees = $user->committees;
        
        $selectedCommitteeId = $request->input('committee_id');
        
        $lessonsQuery = Lesson::active()->with('committee');
        $tasksQuery = Task::where('is_active', true)->with('committee');
        $quizzesQuery = Quiz::where('is_active', true)
            ->where('visibility', 'private')
            ->with('committee');

        if ($selectedCommitteeId) {
            $lessonsQuery->where('committee_id', $selectedCommitteeId);
            $tasksQuery->where('committee_id', $selectedCommitteeId);
            $quizzesQuery->where('committee_id', $selectedCommitteeId);
        } else {
            $committeeIds = $committees->pluck('id');
            $lessonsQuery->whereIn('committee_id', $committeeIds);
            $tasksQuery->whereIn('committee_id', $committeeIds);
            $quizzesQuery->whereIn('committee_id', $committeeIds);
        }

        // Exclude tasks already submitted by the user
        $tasksQuery->whereDoesntHave('submissions', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });

        $recentLessons = $lessonsQuery->latest()->take(5)->get();
        $recentTasks = $tasksQuery->where(function($q) {
            $q->whereNull('deadline')
              ->orWhere('deadline', '>', now());
        })->latest()->take(5)->get();
        
        // Get quizzes and filter out ones user has already participated in
        $allQuizzes = $quizzesQuery->latest()->get();
        $recentQuizzes = $allQuizzes->filter(function ($quiz) use ($user) {
            // Check if user's email has participated in this quiz
            return !QuizCacheService::hasEmailParticipated($quiz->id, $user->email);
        })->take(5);

        // Get sessions for the user's committees
        $sessionsQuery = \App\Models\GoogleSession::query();
        
        if ($selectedCommitteeId) {
            $sessionsQuery->where('committee_id', $selectedCommitteeId);
        } else {
            $sessionsQuery->whereIn('committee_id', $committees->pluck('id'));
        }
        
        $sessions = $sessionsQuery->get();
        
        // Format sessions for calendar
        $now = now();
        $calendarEvents = $sessions->map(function($session) use ($now) {
            $isActive = $session->start_time <= $now && $session->end_time >= $now;
            
            return [
                'id' => $session->id,
                'title' => $session->title,
                'start' => $session->start_time->toIso8601String(),
                'end' => $session->end_time->toIso8601String(),
                'url' => $session->session_url,
                'className' => $isActive ? 'active-meeting-event' : '',
                'extendedProps' => [
                    'isActive' => $isActive,
                    'committeeId' => $session->committee_id,
                    'committeeName' => $session->committee->name ?? 'Unknown Committee',
                    'sessionUrl' => $session->session_url
                ]
            ];
        });
        
        // Check for active meeting (happening right now)
        $activeMeeting = $sessions->filter(function($session) use ($now) {
            return $session->start_time <= $now && $session->end_time >= $now;
        })->first();

        return view('user.dashboard', compact(
            'committees', 
            'recentLessons', 
            'recentTasks', 
            'recentQuizzes', 
            'selectedCommitteeId',
            'calendarEvents',
            'activeMeeting'
        ));
    }
}
