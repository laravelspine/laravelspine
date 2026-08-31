<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Mail\GenericMail;
use Spine\Notifications\GenericMailNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

/**
 * MailService — wrapper around Laravel Mail + Notification.
 *
 * Provides an API for:
 * - Sending email via Mailable (sync or queued)
 * - Sending notifications via the Notification channel
 * - Email queueing, retry, and cleanup
 */
class MailService
{
    /**
     * Send an email using a Mailable.
     *
     * @param  array{to: string, subject: string, view: string, data?: array<string, mixed>, queue?: bool, queue_name?: string|null}  $payload
     */
    public function send(array $payload): bool
    {
        $sending = new \Spine\Events\MailSending($payload);
        event($sending);
        $payload = $sending->payload;

        $to = $payload['to'] ?? null;
        $subject = $payload['subject'] ?? '(no subject)';
        $view = $payload['view'] ?? null;
        $data = $payload['data'] ?? [];
        $queue = (bool) ($payload['queue'] ?? false);

        if (! $to || ! $view) {
            return false;
        }

        $mailable = (new GenericMail($subject, $view, $data))->to($to);

        if ($queue) {
            $queueName = $payload['queue_name'] ?? null;
            $mailable->onQueue($queueName);
            Mail::to($to)->queue($mailable);

            return true;
        }

        Mail::to($to)->send($mailable);

        return true;
    }

    /**
     * Send an SMTP test email.
     *
     * Dispatches MailTesting (mutable payload, veto point) before sending and
     * MailTested (success/error) after. Returns the outcome.
     *
     * @param  array{to: string, subject?: string, body?: string}  $payload
     * @return array{success: bool, error?: string}
     */
    public function testSmtp(array $payload): array
    {
        $testing = new \Spine\Events\MailTesting($payload);
        event($testing);
        $payload = $testing->payload;

        $to = $payload['to'] ?? null;
        $subject = $payload['subject'] ?? 'SMTP Setup Testing';
        $body = $payload['body'] ?? 'This is a test email to verify your SMTP settings.';

        if (! $to) {
            \Spine\Events\MailTested::dispatch(false, 'No recipient provided');

            return ['success' => false, 'error' => 'No recipient provided'];
        }

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            \Spine\Events\MailTested::dispatch(true);

            return ['success' => true];
        } catch (\Throwable $e) {
            $error = $e->getMessage();

            \Spine\Events\MailTested::dispatch(false, $error);

            return ['success' => false, 'error' => $error];
        }
    }

    /**
     * Send a notification to a specific user.
     *
     * @param  array{user_id: int, subject: string, body: string, action_url?: string|null}  $payload
     */
    public function notify(array $payload): bool
    {
        $userId = $payload['user_id'] ?? null;
        $subject = $payload['subject'] ?? '(no subject)';
        $body = $payload['body'] ?? '';
        $actionUrl = $payload['action_url'] ?? null;

        if (! $userId) {
            return false;
        }

        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        $user->notify(new GenericMailNotification($subject, $body, $actionUrl));

        return true;
    }

    /**
     * Broadcast a notification to many users.
     *
     * @param  array{user_ids: list<int>, subject: string, body: string, action_url?: string|null}  $payload
     */
    public function notifyMany(array $payload): int
    {
        $userIds = $payload['user_ids'] ?? [];
        $subject = $payload['subject'] ?? '(no subject)';
        $body = $payload['body'] ?? '';
        $actionUrl = $payload['action_url'] ?? null;

        if (empty($userIds)) {
            return 0;
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            return 0;
        }

        Notification::send($users, new GenericMailNotification($subject, $body, $actionUrl));

        return $users->count();
    }

    /**
     * Retry all failed mail jobs.
     *
     * @return int number of jobs retried
     */
    public function retryQueue(?string $queueName = null): int
    {
        $ids = $this->failedJobIds($queueName);

        if (empty($ids)) {
            return 0;
        }

        foreach ($ids as $id) {
            Queue::retry((int) $id);
        }

        return count($ids);
    }

    /**
     * Clean up old failed mail jobs.
     *
     * @return int number of jobs deleted
     */
    public function cleanUpOldQueue(?string $queueName = null, int $maxAgeDays = 7): int
    {
        $table = $this->failedTable();
        $cutoff = now()->subDays($maxAgeDays);

        $failed = DB::table($table)
            ->when($queueName, fn ($q) => $q->where('queue', $queueName))
            ->where('failed_at', '<', $cutoff->toDateTimeString())
            ->pluck('id');

        $count = $failed->count();

        foreach ($failed as $id) {
            Queue::forget((int) $id);
        }

        return $count;
    }

    /**
     * Check the number of queued emails waiting.
     */
    public function pendingCount(?string $queueName = null): int
    {
        $table = $this->queueTable();

        return (int) DB::table($table)
            ->when($queueName, fn ($q) => $q->where('queue', $queueName))
            ->count();
    }

    /**
     * @return list<int>
     */
    private function failedJobIds(?string $queueName): array
    {
        $table = $this->failedTable();

        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->when($queueName, fn ($q) => $q->where('queue', $queueName))
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();
    }

    private function queueTable(): string
    {
        $connector = config('queue.default');

        return (string) (config("queue.connections.{$connector}.table") ?? 'jobs');
    }

    private function failedTable(): string
    {
        $connector = config('queue.default');

        return (string) (config('queue.failed.table') ?? "{$connector}_failed_jobs");
    }
}
