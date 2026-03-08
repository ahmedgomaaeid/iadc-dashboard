<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Board;
use App\Models\Committee;
use App\Models\ContactMessage;
use App\Models\DynamicFormSubmission;
use App\Models\Event;
use App\Models\Highboard;
use App\Models\Lesson;
use App\Models\NewsletterSubscriber;
use App\Models\Quiz;
use App\Models\Session;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use App\Models\UserEvaluation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ── People counts ──
        $highBoardCount = Highboard::where('is_active', true)->count();
        $boardCount = Board::where('is_active', true)->count();
        $userCount = User::where('is_active', true)->count();
        $committeeCount = Committee::where('is_active', true)->count();

        // ── Tasks stats ──
        $totalTasks = Task::count();
        $activeTasks = Task::where('is_active', true)->count();
        $overdueTasks = Task::where('is_active', true)
            ->where('deadline', '<', Carbon::now())
            ->count();

        // ── Submissions stats ──
        $totalSubmissions = TaskSubmission::count();
        $pendingSubmissions = TaskSubmission::where('status', 'pending')->count();
        $acceptedSubmissions = TaskSubmission::where('status', 'accepted')->count();
        $rejectedSubmissions = TaskSubmission::where('status', 'rejected')->count();

        // ── Sessions stats ──
        $totalSessions = Session::count();
        $upcomingSessions = Session::where('start_time', '>', Carbon::now())->count();
        $completedSessions = Session::where('is_finally_ended', true)->count();

        // ── Content counts ──
        $lessonCount = Lesson::count();
        $quizCount = Quiz::count();
        $articleCount = Article::count();
        $eventCount = Event::count();

        // ── Engagement counts ──
        $formSubmissionCount = DynamicFormSubmission::count();
        $unreadMessages = ContactMessage::where('is_read', false)->count();
        $totalMessages = ContactMessage::count();
        $subscriberCount = NewsletterSubscriber::where('is_active', true)->count();

        // ── Chart: Members per committee ──
        $committees = Committee::where('is_active', true)->withCount('users')->get();
        $committeeMembersLabels = $committees->pluck('name')->toArray();
        $committeeMembersData = $committees->pluck('users_count')->toArray();

        // ── Chart: Task submission status (doughnut) ──
        $submissionStatusLabels = ['Pending', 'Accepted', 'Rejected'];
        $submissionStatusData = [$pendingSubmissions, $acceptedSubmissions, $rejectedSubmissions];

        // ── Chart: Monthly user registration (last 6 months) ──
        $monthlyLabels = [];
        $monthlyUserData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            $monthlyUserData[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        // ── Chart: Content overview (horizontal bar) ──
        $contentLabels = ['Lessons', 'Quizzes', 'Articles', 'Events'];
        $contentData = [$lessonCount, $quizCount, $articleCount, $eventCount];

        // ── Recent tasks approaching deadline ──
        $upcomingTasks = Task::where('is_active', true)
            ->where('deadline', '>=', Carbon::now())
            ->orderBy('deadline', 'asc')
            ->limit(5)
            ->get();

        // ── Latest submissions ──
        $latestSubmissions = TaskSubmission::with(['user', 'task'])
            ->latest()
            ->limit(5)
            ->get();

        return view('supervisor.dashboard.index', compact(
            'highBoardCount',
            'boardCount',
            'userCount',
            'committeeCount',
            'totalTasks',
            'activeTasks',
            'overdueTasks',
            'totalSubmissions',
            'pendingSubmissions',
            'acceptedSubmissions',
            'rejectedSubmissions',
            'totalSessions',
            'upcomingSessions',
            'completedSessions',
            'lessonCount',
            'quizCount',
            'articleCount',
            'eventCount',
            'formSubmissionCount',
            'unreadMessages',
            'totalMessages',
            'subscriberCount',
            'committeeMembersLabels',
            'committeeMembersData',
            'submissionStatusLabels',
            'submissionStatusData',
            'monthlyLabels',
            'monthlyUserData',
            'contentLabels',
            'contentData',
            'upcomingTasks',
            'latestSubmissions'
        ));
    }
}
