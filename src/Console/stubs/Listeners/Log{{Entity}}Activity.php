<?php

declare(strict_types=1);

namespace Modules\{{Studly}}\Listeners;

use Modules\{{Studly}}\Models\{{Entity}};
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

/**
 * HOOK — entity lifecycle generic (HasLifecycleHooks) untuk {{Entity}}.
 *
 * 1. created/updated/deleted -> activity log (satu listener, semua entity).
 * 2. STATUS-CHANGE pattern: EntityUpdated mengecek changes['status'] /
 *    changes['is_active'] — padanan status_changed di legacy.
 */
class Log{{Entity}}Activity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof {{Entity}}) {
            return;
        }

        $this->activityLog->log(
            "{{Entity}} created: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof {{Entity}}) {
            return;
        }

        $changes = $event->changes;

        $this->activityLog->log(
            "{{Entity}} updated: " . $this->label($event->entity) . " (" . $this->describe($changes) . ")",
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $changes],
        );

        $status = $changes['status'] ?? $changes['is_active'] ?? null;
        if ($status && $status['old'] !== $status['new']) {
            $this->activityLog->log(
                "{{Entity}} status changed: {$status['old']} -> {$status['new']}",
                $event->entity,
                $this->user(),
                ['event' => '{{entity}}.status_changed', 'old' => $status['old'], 'new' => $status['new']],
            );
        }
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof {{Entity}}) {
            return;
        }

        $this->activityLog->log(
            "{{Entity}} deleted: " . $this->label($event->entity),
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    private function describe(array $changes): string
    {
        $parts = [];

        foreach ($changes as $field => $change) {
            if (in_array($field, ['updated_at', 'remember_token'], true)) {
                continue;
            }

            $label = (method_exists({{Entity}}::class, 'labels') ? ({{Entity}}::labels()[$field] ?? $field) : $field);
            $parts[] = $label . ': ' . $change['old'] . ' → ' . $change['new'];
        }

        return implode(', ', $parts);
    }

    private function label($entity): string
    {
        return (string) ($entity->name ?? $entity->getKey());
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}