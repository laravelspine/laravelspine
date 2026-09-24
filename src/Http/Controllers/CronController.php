<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CronController extends \Spine\Http\Controllers\Controller
{
    /**
     * Trigger the application cron shell via Artisan.
     *
     * Requires the 'system.settings' permission.
     */
    public function run(Request $request): JsonResponse
    {
        if (! $request->user()->can('system.settings')) {
            return $this->forbidden();
        }

        $exitCode = 0;
        $output = [];

        $command = base_path('artisan') . ' app:cron --json';
        exec($command, $output, $exitCode);

        $result = [];
        foreach ($output as $line) {
            $decoded = json_decode($line, true);
            if ($decoded !== null) {
                $result = $decoded;
                break;
            }
        }

        if (empty($result)) {
            $result = [
                'success' => $exitCode === 0,
                'tasks' => [],
                'message' => $exitCode === 0 ? 'Cron completed successfully.' : 'Cron completed with errors.',
            ];
        }

        return $this->ok($result);
    }

    private function forbidden(): JsonResponse
    {
        return response()->json(['message' => 'Forbidden'], 403);
    }
}
