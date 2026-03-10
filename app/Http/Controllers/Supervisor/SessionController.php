<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\GoogleSession;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Curated color palette for committee-based event coloring.
     */
    private $committeeColors = [
        '#667eea', // Indigo
        '#11998e', // Teal
        '#f5576c', // Rose
        '#4facfe', // Sky Blue
        '#a18cd1', // Lavender
        '#fa709a', // Pink
        '#f6d365', // Gold
        '#00b4d8', // Cyan
        '#e76f51', // Coral
        '#2d6a4f', // Forest
        '#7209b7', // Purple
        '#ff6b6b', // Red
        '#48bfe3', // Light Blue
        '#06d6a0', // Mint
        '#f77f00', // Orange
    ];

    public function index()
    {
        $now = Carbon::now();

        // Get all committees for color mapping
        $committees = Committee::where('is_active', true)->orderBy('id')->get();

        // Build committee → color map
        $committeeColorMap = [];
        foreach ($committees as $i => $committee) {
            $committeeColorMap[$committee->id] = $this->committeeColors[$i % count($this->committeeColors)];
        }

        // Get ALL google sessions across all committees
        $sessions = GoogleSession::with('committee')->orderBy('start_time', 'desc')->get();

        // Format for FullCalendar
        $calendarEvents = $sessions->map(function ($session) use ($now, $committeeColorMap) {
            $isActive = $session->start_time <= $now && $session->end_time >= $now;
            $isPast = $session->end_time < $now;
            $color = $committeeColorMap[$session->committee_id] ?? '#6c757d';

            return [
                'id' => $session->id,
                'title' => $session->title,
                'start' => $session->start_time->toIso8601String(),
                'end' => $session->end_time->toIso8601String(),
                'backgroundColor' => $isActive ? '#ff4444' : $color,
                'borderColor' => $isActive ? '#ff4444' : $color,
                'textColor' => '#ffffff',
                'className' => $isActive ? 'active-meeting-event' : ($isPast ? 'past-event' : ''),
                'extendedProps' => [
                    'isActive' => $isActive,
                    'isPast' => $isPast,
                    'committeeName' => $session->committee->name ?? 'Unknown Committee',
                    'committeeId' => $session->committee_id,
                    'description' => $session->description ?? '',
                ],
            ];
        });

        // Stats
        $totalSessions = $sessions->count();
        $upcomingSessions = $sessions->where('start_time', '>', $now)->count();
        $completedSessions = $sessions->where('end_time', '<', $now)->count();
        $liveSessions = $sessions->filter(fn($s) => $s->start_time <= $now && $s->end_time >= $now)->count();

        // Committees with colors for legend
        $committeeList = $committees->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'color' => $committeeColorMap[$c->id],
        ]);

        return view('supervisor.sessions.index', compact(
            'calendarEvents',
            'committeeList',
            'totalSessions',
            'upcomingSessions',
            'completedSessions',
            'liveSessions'
        ));
    }
}
