<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\MailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Email and notification sending API.
 *
 * @group api/v1
     * @subgroup Mail
 */
class MailController extends Controller
{
    public function __construct(
        private MailService $mail
    ) {}

    /**
     * Send an email using a Mailable.
     *
     * @authenticated
     *
     * @bodyParam to string required Recipient email address. Example: user@example.com
     * @bodyParam subject string required Email subject. Example: Invoice #123
     * @bodyParam view string required Blade view name for the email template. Example: emails.invoice
     * @bodyParam data array<string, mixed> optional Data for the template. Example: {"invoice_id": 123}
     *
     * @response scenario=success {"message":"Email sent","success":true}
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'view' => 'required|string',
            'data' => 'sometimes|array',
            'queue' => 'sometimes|boolean',
            'queue_name' => 'sometimes|nullable|string|max:120',
        ]);

        $success = $this->mail->send([
            'to' => $validated['to'],
            'subject' => $validated['subject'],
            'view' => $validated['view'],
            'data' => $validated['data'] ?? [],
            'queue' => (bool) ($validated['queue'] ?? false),
            'queue_name' => $validated['queue_name'] ?? null,
        ]);

        return response()->json([
            'message' => $success ? 'Email queued/sent' : 'Failed to send email',
            'success' => $success,
        ], $success ? 200 : 500);
    }

    /**
     * Send an SMTP test email.
     *
     * @authenticated
     *
     * @bodyParam to string required Recipient email address. Example: user@example.com
     * @bodyParam subject string optional Email subject. Example: SMTP Setup Testing
     * @bodyParam body string optional Email body.
     *
     * @response scenario=success {"message":"SMTP test email sent","success":true}
     * @response scenario=failure {"message":"SMTP test failed","success":false,"error":"..."}
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
        ]);

        $result = $this->mail->testSmtp([
            'to' => $validated['to'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'] ?? null,
        ]);

        return response()->json([
            'message' => $result['success'] ? 'SMTP test email sent' : 'SMTP test failed',
            'success' => $result['success'],
            'error' => $result['error'] ?? null,
        ], $result['success'] ? 200 : 500);
    }

    /**
     * Send an email notification to a user.
     *
     * @authenticated
     *
     * @bodyParam user_id int required Recipient user ID. Example: 1
     * @bodyParam subject string required Notification subject. Example: Task assigned
     * @bodyParam body string required Notification body. Example: You have a new task
     * @bodyParam action_url string optional Action URL. Example: https://app.example.com/tasks/1
     *
     * @response scenario=success {"message":"Notification sent","success":true}
     */
    public function notify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'action_url' => 'sometimes|nullable|url',
        ]);

        $success = $this->mail->notify([
            'user_id' => (int) $validated['user_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'action_url' => $validated['action_url'] ?? null,
        ]);

        return response()->json([
            'message' => $success ? 'Notification sent' : 'Failed to send notification',
            'success' => $success,
        ], $success ? 200 : 500);
    }

    /**
     * Send an email notification to many users.
     *
     * @authenticated
     *
     * @bodyParam user_ids array<int> required List of recipient user IDs. Example: [1,2,3]
     * @bodyParam subject string required Notification subject. Example: Important announcement
     * @bodyParam body string required Notification body.
     * @bodyParam action_url string optional Action URL.
     *
     * @response scenario=success {"message":"Notification sent","success":true,"recipients":3}
     */
    public function notifyMany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'action_url' => 'sometimes|nullable|url',
        ]);

        $count = $this->mail->notifyMany([
            'user_ids' => $validated['user_ids'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'action_url' => $validated['action_url'] ?? null,
        ]);

        return response()->json([
            'message' => 'Notification sent',
            'success' => $count > 0,
            'recipients' => $count,
        ], $count > 0 ? 200 : 500);
    }

    /**
     * Mail queue: retry failed mail jobs.
     *
     * @authenticated
     *
     * @queryParam queue string Queue name (optional). Example: emails
     *
     * @response scenario=success {
     *   "message": "Retried 2 failed job(s)", "success": true, "retried": 2
     * }
     */
    public function retryQueue(Request $request): JsonResponse
    {
        $queue = $request->query('queue') ?: null;

        $count = $this->mail->retryQueue($queue);

        return response()->json([
            'message' => "Retried {$count} failed job(s)",
            'success' => true,
            'retried' => $count,
        ]);
    }

    /**
     * Mail queue: clean up old failed jobs.
     *
     * @authenticated
     *
     * @queryParam queue string Queue name (optional). Example: emails
     * @queryParam days integer Max age of failed jobs in days. Example: 7
     *
     * @response scenario=success {
     *   "message": "Cleaned 5 old failed job(s)", "success": true, "cleaned": 5
     * }
     */
    public function cleanUpQueue(Request $request): JsonResponse
    {
        $queue = $request->query('queue') ?: null;
        $days = (int) ($request->query('days', 7));

        $count = $this->mail->cleanUpOldQueue($queue, max(1, $days));

        return response()->json([
            'message' => "Cleaned {$count} old failed job(s)",
            'success' => true,
            'cleaned' => $count,
        ]);
    }

    /**
     * Mail queue: number of pending jobs.
     *
     * @authenticated
     *
     * @queryParam queue string Queue name (optional). Example: emails
     *
     * @response scenario=success {
     *   "data": { "pending": 3 }
     * }
     */
    public function queueStatus(Request $request): JsonResponse
    {
        $queue = $request->query('queue') ?: null;

        return response()->json([
            'data' => [
                'pending' => $this->mail->pendingCount($queue),
            ],
        ]);
    }
}
