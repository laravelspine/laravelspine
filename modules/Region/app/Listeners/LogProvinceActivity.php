<?php

declare(strict_types=1);

namespace Modules\Region\app\Listeners;

use Modules\Region\app\Models\Province;
use Modules\Region\app\Models\Regency;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

class LogProvinceActivity
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof Province) {
            return;
        }

        $this->activityLog->log(
            "Province created: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof Province) {
            return;
        }

        $changes = $event->changes;

        $this->activityLog->log(
            "Province updated: " . $this->label($event->entity) . " (" . $this->describe($changes) . ")",
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $changes],
        );
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof Province) {
            return;
        }

        $this->activityLog->log(
            "Province deleted: " . $this->label($event->entity),
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

            $label = (method_exists(Province::class, 'labels') ? (Province::labels()[$field] ?? $field) : $field);
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