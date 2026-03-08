<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\ReminderLog;
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

        // Get active tasks where deadline is in the future and within the next 24 hours
        // Skip tasks where deadline has already passed
        $tasks = Task::where('is_active', true)
            ->whereNotNull('deadline')
            ->where('deadline', '>', $now)
            ->where('deadline', '<=', $next24Hours)
            ->with('committee.users')
            ->get();

        $sentCount = 0;

        foreach ($tasks as $task) {
            // Get IDs of users who already submitted this task
            $submittedUserIds = TaskSubmission::where('task_id', $task->id)
                ->pluck('user_id')
                ->toArray();

            // Get IDs of users who already received a reminder for this task
            $remindedUserIds = ReminderLog::where('type', 'task')
                ->where('related_id', $task->id)
                ->pluck('user_id')
                ->toArray();

            $excludeIds = array_unique(array_merge($submittedUserIds, $remindedUserIds));

            $members = $task->committee->users()
                ->where('is_active', true)
                ->whereNotIn('users.id', $excludeIds)
                ->get();

            $deadlineFormatted = $task->deadline->format('Y-m-d h:i A');

            $message = "⏰ تذكير بالتاسك \n"
                . "📋 {$task->title} \n"
                . "⏳ الموعد النهائي: {$deadlineFormatted} \n"
                . "ادخل على الـ Dashboard وسلم التاسك قبل الموعد. \n"
                . "🔗 " . route('tasks.show', $task->id);

            foreach ($members as $member) {
                try {
                    WhatsAppService::send($member->phone, $message);

                    // Log that reminder was sent
                    ReminderLog::create([
                        'type' => 'task',
                        'related_id' => $task->id,
                        'user_id' => $member->id,
                    ]);

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
