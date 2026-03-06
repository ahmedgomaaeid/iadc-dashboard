<?php

namespace App\Console\Commands;

use App\Models\Session;
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

        // Get sessions where start_time is between now and now + 1 hour
        // Exclude sessions that have already ended
        $sessions = Session::whereNull('end_time')
            ->where('is_finally_ended', false)
            ->whereBetween('start_time', [$now, $nextHour])
            ->with('committee.users')
            ->get();

        $sentCount = 0;

        foreach ($sessions as $session) {
            $members = $session->committee->users()->where('is_active', true)->get();
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
