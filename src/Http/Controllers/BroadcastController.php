<?php

namespace Spine\Http\Controllers;

use Spine\Events\NotificationSent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Realtime — Laravel Broadcasting + Reverb.
 * Clients: laravel-echo + pusher-js pointed at the Reverb server; channel auth
 * via `POST /api/v1/broadcasting/auth` (Sanctum token).
 *
 * @group api/v1
 * @subgroup Broadcasting
 */
class BroadcastController extends Controller
{
    /**
     * Send a realtime notification to the currently logged-in user.
     *
     * Triggers the `notification.sent` event on the private channel `user.{id}`.
     * The frontend (Next.js) listens to that channel and displays a
     * desktop notification via the browser Notification API.
     *
     * @authenticated
     *
     * @bodyParam title string optional Notification title. Example: New message
     * @bodyParam message string optional Notification body. Example: You have a new message
     * @bodyParam type string optional Notification type: info|success|warning|error. Example: success
     * @bodyParam data array optional Extra data (JSON object).
     *
     * @response 200 scenario="sent" {"success": true, "event": "notification.sent", "channel": "user.1", "payload": {"title": "New message", "message": "You have a new message", "type": "success", "data": [], "sent_at": "2026-08-29T00:00:00+07:00"}}
     * @response 422 {"message": "The given data was invalid.", "errors": {"type": ["The selected type is invalid."]}}
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:190'],
            'message' => ['sometimes', 'string', 'max:1000'],
            'type' => ['sometimes', 'in:info,success,warning,error'],
            'data' => ['sometimes', 'array'],
        ]);

        $user = $request->user();
        $event = new NotificationSent(
            userId: (int) $user->id,
            title: $validated['title'] ?? 'Notifikasi baru',
            message: $validated['message'] ?? 'Ini adalah notifikasi uji realtime.',
            type: $validated['type'] ?? 'info',
            data: $validated['data'] ?? [],
        );

        broadcast($event);

        return response()->json([
            'success' => true,
            'event' => $event->broadcastAs(),
            'channel' => 'user.'.$user->id,
            'payload' => $event->broadcastWith(),
        ]);
    }

    /**
     * Realtime connection configuration for the frontend.
     *
     * Returns public parameters (no secrets) so clients can
     * connect laravel-echo to the Reverb server.
     *
     * @authenticated
     *
     * @response 200 {"driver": "reverb", "key": "a0adc25fe8777223fb4a", "scheme": "http", "host": "api.example.com", "port": 8080}
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'driver' => 'reverb',
            'key' => config('reverb.apps.apps.0.key'),
            'scheme' => config('reverb.apps.apps.0.options.scheme', 'http'),
            'host' => config('reverb.apps.apps.0.options.host', '127.0.0.1'),
            'port' => (int) config('reverb.apps.apps.0.options.port', 8080),
        ]);
    }
}
