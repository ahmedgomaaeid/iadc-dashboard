<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Models\ReminderLog;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSessionReminder extends Command
{
    protected $signature = 'reminders:sessions';
    protected $description = 'Send WhatsApp reminders for sessions starting within the next 1 hour';

    public function handle()
    {
        $now = now();
        $nextHour = now()->addHour();

        // Get sessions starting within the next hour that haven't finished
        // Skip sessions where start_time has already passed or session is ended
        $sessions = Session::whereNull('end_time')
            ->where('is_finally_ended', false)
            ->where('start_time', '>', $now)
            ->where('start_time', '<=', $nextHour)
            ->with('committee.users')
            ->get();

        $sentCount = 0;

        foreach ($sessions as $session) {
            // Get IDs of users who already received a reminder for this session
            $remindedUserIds = ReminderLog::where('type', 'session')
                ->where('related_id', $session->id)
                ->pluck('user_id')
                ->toArray();

            $members = $session->committee->users()
                ->where('is_active', true)
                ->whereNotIn('users.id', $remindedUserIds)
                ->get();

            $startFormatted = $session->start_time->format('h:i A');

            $joinUrl = $session->zoom_join_url ?? $session->meeting_link ?? '';
            $linkLine = $joinUrl ? "🔗 {$joinUrl}" : '';

            $message = "📢 تذكير بالاجتماع \n"
                . "📌 {$session->title} \n"
                . "🕐 الساعة: {$startFormatted} \n"
                . "جهز نفسك وادخل على اللينك في الوقت. \n"
                . $linkLine;

            foreach ($members as $member) {
                try {
                    WhatsAppService::send($member->phone, $message);

                    // Log that reminder was sent
                    ReminderLog::create([
                        'type' => 'session',
                        'related_id' => $session->id,
                        'user_id' => $member->id,
                    ]);

                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Error sending session reminder to {$member->phone}: " . $e->getMessage());
                }
            }
        }

        $this->info("Session reminders sent: {$sentCount} messages for {$sessions->count()} sessions.");
        return Command::SUCCESS;
    }
}
