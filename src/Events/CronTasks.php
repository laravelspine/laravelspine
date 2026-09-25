<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect cron tasks from core + modules.
 *
 * Listener returns array of task definitions:
 *   ['name' => 'mail.cleanup', 'interval' => 'daily', 'handler' => 'MailCleanupCommand::class']
 */
class CronTasks
{
    use Dispatchable;

    public array $tasks = [];

    public function addTask(array $task): void
    {
        $this->tasks[] = $task;
    }
}