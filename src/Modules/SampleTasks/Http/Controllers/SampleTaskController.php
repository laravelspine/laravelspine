<?php

declare(strict_types=1);

namespace Modules\SampleTasks\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\SampleTasks\Models\SampleTask;
use Spine\Services\ActivityLogService;

/**
 * CONTOH API — CRUD child entity SampleTask.
 *
 * Endpoint:
 *   GET    /api/v1/sample-tasks?sample_item_id={id}
 *   POST   /api/v1/sample-tasks
 *   GET    /api/v1/sample-tasks/{id}
 *   PUT    /api/v1/sample-tasks/{id}
 *   DELETE /api/v1/sample-tasks/{id}
 *
 * Activity log TIDAK manual — otomatis via EntityCreated/Updated/Deleted
 * (HasLifecycleHooks) -> listener LogTaskActivity.
 */
class SampleTaskController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = SampleTask::query();

        // Filter child: ?sample_item_id={id} — list tasks milik satu SampleItem.
        if ($request->has('sample_item_id')) {
            $query->where('sample_item_id', (int) $request->query('sample_item_id'));
        }

        return response()->json(['data' => $query->orderByDesc('id')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sample_item_id' => ['required', 'integer', 'exists:sample_items,id'],
            'title'          => ['required', 'string', 'max:190'],
            'status'         => ['sometimes', 'string', 'in:pending,in_progress,done'],
        ]);

        $task = SampleTask::create($validated);

        Log::info('[SampleTask] created', ['id' => $task->id, 'title' => $task->title]);

        return response()->json($task, 201);
    }

    public function show(int $id): JsonResponse
    {
        $task = SampleTask::find($id);

        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $task = SampleTask::find($id);

        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $validated = $request->validate([
            'sample_item_id' => ['sometimes', 'integer', 'exists:sample_items,id'],
            'title'          => ['sometimes', 'string', 'max:190'],
            'status'         => ['sometimes', 'string', 'in:pending,in_progress,done'],
        ]);

        $task->update($validated);

        Log::info('[SampleTask] updated', ['id' => $task->id, 'title' => $task->title]);

        return response()->json($task);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = SampleTask::find($id);

        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    public function activityLogs(int $id): JsonResponse
    {
        if (! SampleTask::find($id)) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', SampleTask::class)
            ->where('subject_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($log) => [
                'id'          => $log->id,
                'description' => $log->description,
                'causer'      => $log->causer?->name ?? 'System',
                'properties'  => $log->properties,
                'at'          => $log->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $logs]);
    }
}
