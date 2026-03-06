<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTaskReminder extends Command
{
    protected $signature = 'reminders:tasks';
    protected $description = 'Send WhatsApp reminders for tasks with deadlines within the next 24 hours';

    public function handle()
    {
        $now = now();
        $next24Hours = now()->addHours(24);

        // Get active tasks where deadline is between now and now + 24 hours
        $tasks = Task::where('is_active', true)
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [$now, $next24Hours])
            ->with('committee.users')
            ->get();

        $sentCount = 0;

        foreach ($tasks as $task) {
            $members = $task->committee->users()->where('is_active', true)->get();
            $deadlineFormatted = $task->deadline->format('Y-m-d h:i A');

            $message = "⏰ تذكير بالتاسك \n"
                . "📋 {$task->title} \n"
                . "⏳ الموعد النهائي: {$deadlineFormatted} \n"
                . "ادخل على الـ Dashboard وسلم التاسك قبل الموعد. \n"
                . "🔗 " . route('tasks.show', $task->id);

            foreach ($members as $member) {
                try {
                    WhatsAppService::send($member->phone, $message);
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error("Error sending task reminder to {$member->phone}: " . $e->getMessage());
                }
            }
        }

        $this->info("Task reminders sent: {$sentCount} messages for {$tasks->count()} tasks.");
        return Command::SUCCESS;
    }
}
