<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Spine\Models\ActivityLog;
use Spine\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Activity log API.
 *
 * This is a REST resource (each log has an auto-increment ID), unlike
 * Settings which are key-value. Supports multi-tenant scope.
 *
 * @group api/v1
     * @subgroup Activity Logs
 */
class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activity
    ) {}

    /**
     * List activity logs (server-side list: paging, sort, filter, search, include).
     *
     * Server-side list contract translated to REST query params:
     *   ?sort=-created_at,id            (whitelist, prefix - = DESC)
     *   &filter[tenant_id]=1            (exact)
     *   &filter[causer_id]=1            (exact)
     *   &filter[subject_type]=...       (exact)
     *   &filter[subject_id]=5           (exact)
     *   &filter[description]=invoice    (partial / LIKE)
     *   &search=...                     (global search: description + subject_type)
     *   &include=causer,tenant          (eager load relations)
     *   &per_page=25&page=2             (pagination)
     *
     * @authenticated
     *
     * @queryParam sort string optional Sort column (whitelist): id, description, subject_type, subject_id, causer_id, tenant_id, created_at; prefix - for DESC. Example: -created_at
     * @queryParam filter[tenant_id] integer optional Exact-match filter on tenant. Example: 1
     * @queryParam filter[causer_id] integer optional Exact-match filter on the causing user. Example: 1
     * @queryParam filter[subject_type] string optional Exact-match filter on subject type. Example: Spine\Models\Invoice
     * @queryParam filter[subject_id] integer optional Exact-match filter on subject ID. Example: 5
     * @queryParam filter[description] string optional Partial (LIKE) filter on description. Example: invoice
     * @queryParam search string optional Global search across description and subject_type. Example: invoice
     * @queryParam include string optional Relations to eager-load: causer,subject,tenant (comma-separated). Example: causer,tenant
     * @queryParam per_page integer optional Items per page (max 100). Example: 25
     * @queryParam page integer optional Page. Example: 2
     *
     * @response scenario=success {
     *   "data": [
     *     {
     *       "id": 1,
     *       "description": "Invoice dibuat",
     *       "subject_type": "Spine\\Models\\Invoice",
     *       "subject_id": 5,
     *       "causer_type": "Spine\\Models\\User",
     *       "causer_id": 1,
     *       "tenant_id": 1,
     *       "properties": {"ip": "127.0.0.1"},
     *       "created_at": "2026-08-28T00:00:00+00:00"
     *     }
     *   ],
     *   "links": {"first": "...?page=1", "last": "...?page=3", "next": "...?page=2", "prev": null},
     *   "meta": {
     *     "current_page": 2, "from": 26, "last_page": 3, "per_page": 25, "to": 50,
     *     "total": 80, "total_filtered": 50
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->query('tenant_id') !== null ? (int) $request->query('tenant_id') : null;

        // total before filtering (equivalent to meta.total)
        $total = $this->activity->query($tenantId)->count();

        $query = QueryBuilder::for($this->activity->query($tenantId))
            ->allowedSorts([
                'id', 'description', 'subject_type', 'subject_id',
                'causer_id', 'tenant_id', 'created_at',
            ])
            ->allowedFilters([
                AllowedFilter::exact('tenant_id'),
                AllowedFilter::exact('causer_id'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::exact('subject_id'),
                AllowedFilter::partial('description'),
            ])
            ->allowedIncludes(['causer', 'subject', 'tenant'])
            ->defaultSort('-created_at');

        // global search
        if ($request->filled('search')) {
            $term = $request->query('search');
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('subject_type', 'like', "%{$term}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $logs = $query->paginate($perPage, ['*'], 'page', (int) $request->query('page', 1))->withQueryString();

        return response()->json([
            'data' => $logs->items(),
            'links' => [
                'first' => $logs->url(1),
                'last' => $logs->url($logs->lastPage()),
                'next' => $logs->nextPageUrl(),
                'prev' => $logs->previousPageUrl(),
            ],
            'meta' => [
                'current_page' => $logs->currentPage(),
                'from' => $logs->firstItem(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'to' => $logs->lastItem(),
                'total' => $total,
                'total_filtered' => $logs->total(),
            ],
        ]);
    }

    /**
     * Get a single activity log.
     *
     * @authenticated
     *
     * @urlParam id integer required Log ID. Example: 1
     *
     * @response scenario=success {
     *   "id": 1,
     *   "description": "Invoice dibuat",
     *   "subject_type": "Spine\\Models\\Invoice",
     *   "subject_id": 5,
     *   "causer_type": "Spine\\Models\\User",
     *   "causer_id": 1,
     *   "tenant_id": 1,
     *   "properties": {"ip": "127.0.0.1"},
     *   "created_at": "2026-08-28T00:00:00+00:00"
     * }
     * @response status=404 scenario="not found" {
     *   "message": "Activity log not found"
     * }
     */
    public function show(int $id): JsonResponse
    {
        $log = ActivityLog::find($id);

        if (!$log) {
            return response()->json(['message' => 'Activity log not found'], 404);
        }

        return response()->json($log);
    }

    /**
     * Record a new activity log entry.
     *
     * @authenticated
     *
     * @bodyParam description string required Activity description. Example: Invoice dibuat
     * @bodyParam subject_type string optional Subject type (FQCN). Example: Spine\Models\Invoice
     * @bodyParam subject_id integer optional Subject ID. Example: 5
     * @bodyParam causer_id integer optional ID of the causing user. Example: 1
     * @bodyParam tenant_id integer optional Tenant scope. Example: 1
     * @bodyParam properties object optional Additional data. Example: {"ip": "127.0.0.1"}
     *
     * @response scenario=success {
     *   "id": 1,
     *   "description": "Invoice dibuat",
     *   "subject_type": "Spine\\Models\\Invoice",
     *   "subject_id": 5,
     *   "causer_type": "Spine\\Models\\User",
     *   "causer_id": 1,
     *   "tenant_id": 1,
     *   "properties": {"ip": "127.0.0.1"},
     *   "created_at": "2026-08-28T00:00:00+00:00"
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
            'subject_type' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer'],
            'causer_id' => ['nullable', 'integer'],
            'tenant_id' => ['nullable', 'integer'],
            'properties' => ['nullable', 'array'],
        ]);

        $log = $this->activity->log(
            $validated['description'],
            isset($validated['subject_id']) ? (int) $validated['subject_id'] : null,
            isset($validated['causer_id']) ? (int) $validated['causer_id'] : null,
            $validated['properties'] ?? [],
            $validated['tenant_id'] ?? null,
            $validated['subject_type'] ?? null,
            $validated['causer_type'] ?? null
        );

        return response()->json($log, 201);
    }

    /**
     * Delete an activity log.
     *
     * @authenticated
     *
     * @urlParam id integer required Log ID. Example: 1
     *
     * @response scenario=success {
     *   "message": "Activity log deleted"
     * }
     * @response status=404 scenario="not found" {
     *   "message": "Activity log not found"
     * }
     */
    public function destroy(int $id): JsonResponse
    {
        $log = ActivityLog::find($id);

        if (!$log) {
            return response()->json(['message' => 'Activity log not found'], 404);
        }

        $log->delete();

        return response()->json(['message' => 'Activity log deleted']);
    }
}
