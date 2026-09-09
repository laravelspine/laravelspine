<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API notifikasi bell dashboard (scope pribadi, auth saja — data milik user sendiri).
 *
 * @group api/v1
 * @subgroup Notifications
 */
class NotificationController extends Controller
{
    /**
     * Daftar notifikasi user yang login (desc, paginated) + unread_count di meta.
     *
     * @queryParam per_page integer optional Items per page (max 100). Example: 15
     * @queryParam page integer optional Page. Example: 1
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(min((int) $request->query('per_page', 15), 100), ['*'], 'page', (int) $request->query('page', 1))
            ->withQueryString();

        return response()->json([
            'data' => $notifications->items(),
            'links' => [
                'first' => $notifications->url(1),
                'last' => $notifications->url($notifications->lastPage()),
                'next' => $notifications->nextPageUrl(),
                'prev' => $notifications->previousPageUrl(),
            ],
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Tandai satu notifikasi (milik user sendiri) sebagai dibaca.
     *
     * @urlParam id string required Notification ID (uuid). Example: 3f7c...
     */
    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->first();

        if (! $notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json($notification);
    }

    /**
     * Tandai SEMUA notifikasi user sebagai dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
