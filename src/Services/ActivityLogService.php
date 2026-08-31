<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Activity log service.
 *
 * Records model activity and provides scoped queries over the log.
 *
 */
class ActivityLogService
{
    /**
     * Record an activity.
     *
     * @param string $description Short description of the activity.
     * @param Model|int|null $subject  Entity that is the subject (e.g. a modified Invoice).
     * @param Model|int|null $causer  User who performed the activity.
     * @param array $properties Additional data (JSON).
     * @param int|null $tenantId Tenant scope (null = global).
     * @return \Spine\Models\ActivityLog
     */
    public function log(
        string $description,
        mixed $subject = null,
        mixed $causer = null,
        array $properties = [],
        ?int $tenantId = null,
        ?string $subjectType = null,
        ?string $causerType = null
    ): ActivityLog {
        $subjectType = $subjectType;
        $subjectId = null;

        if ($subject instanceof Model) {
            $subjectType = get_class($subject);
            $subjectId = $subject->getKey();
        } elseif (is_int($subject)) {
            $subjectId = $subject;
        }

        $causerType = $causerType;
        $causerId = null;

        if ($causer instanceof Model) {
            $causerType = get_class($causer);
            $causerId = $causer->getKey();
        } elseif (is_int($causer)) {
            $causerId = $causer;
        }

        return ActivityLog::create([
            'description'   => $description,
            'subject_type'  => $subjectType,
            'subject_id'    => $subjectId,
            'causer_type'   => $causerType,
            'causer_id'     => $causerId,
            'tenant_id'     => $tenantId,
            'properties'    => $properties,
        ]);
    }

    /**
     * Query all logs (with optional filter).
     *
     * @param int|null $tenantId Tenant scope.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(?int $tenantId = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = ActivityLog::query();

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
