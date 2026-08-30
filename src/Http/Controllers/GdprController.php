<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Services\GdprService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API untuk GDPR / data privacy.
 *
 * Diadopsi dari `gdpr/` legacy CRM.
 * Data subject = model auth app (`config('auth.providers.users.model')`).
 *
 * @group api/v1
 *     * @subgroup GDPR
 */
class GdprController extends Controller
{
    public function __construct(
        private GdprService $gdpr
    ) {}

    /**
     * Export semua data user.
     *
     * @authenticated
     *
     * @queryParam user_id int required ID user. Example: 1
     *
     * @response scenario=success {
     *   "data": {
     *     "user": {"id": 1, "name": "...", "email": "..."},
     *     "meta": [],
     *     "attachments": [],
     *     "activity_logs": []
     *   }
     * }
     */
    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate(['user_id' => ['required', 'integer']]);

        $userClass = $this->gdpr->userClass();
        $user = $userClass::findOrFail((int) $validated['user_id']);

        return response()->json(['data' => $this->gdpr->export($user)]);
    }

    /**
     * Anonymize data user.
     *
     * @authenticated
     *
     * @bodyParam user_id int required ID user. Example: 1
     *
     * @response scenario=success {"message":"User anonymized","success":true}
     * @response status=404 scenario=not-found {"message":"User not found"}
     */
    public function anonymize(Request $request): JsonResponse
    {
        $validated = $request->validate(['user_id' => ['required', 'integer']]);

        $userClass = $this->gdpr->userClass();
        $user = $userClass::find((int) $validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $success = $this->gdpr->anonymize($user);

        return response()->json([
            'message' => $success ? 'User anonymized' : 'Failed to anonymize user',
            'success' => $success,
        ], $success ? 200 : 500);
    }

    /**
     * Hapus data user permanently.
     *
     * @authenticated
     *
     * @bodyParam user_id int required ID user. Example: 1
     *
     * @response scenario=success {"message":"User deleted","success":true}
     * @response status=404 scenario=not-found {"message":"User not found"}
     */
    public function delete(Request $request): JsonResponse
    {
        $validated = $request->validate(['user_id' => ['required', 'integer']]);

        $userClass = $this->gdpr->userClass();
        $user = $userClass::find((int) $validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $success = $this->gdpr->delete($user);

        return response()->json([
            'message' => $success ? 'User deleted' : 'Failed to delete user',
            'success' => $success,
        ], $success ? 200 : 500);
    }
}
