<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Exceptions\RelationTypeNotRegisteredException;
use Closure;

/**
 * RelationService — the core resolver for relations between entities.
 *
 * The core does not know specific domains (customer/project/lead/...).
 * Modules (e.g. Sales) define per-type resolvers via a hook:
 *   RelationService::registerResolver('customer', fn (int $id) => [...]);
 *
 * The core only:
 *   - stores the type => resolver mapping
 *   - validates that the type is registered (opt-in, prevents leakage)
 *   - calls the resolver when requested
 */
class RelationService
{
    /**
     * @var array<string, Closure(int):array<string, mixed>>
     */
    private array $resolvers = [];

    /**
     * Register a resolver for a given relation type.
     * Called by modules through a hook (ServiceProvider::boot).
     *
     * @param string $type  e.g. 'customer', 'project', 'lead'
     * @param Closure(int $id): array<string, mixed> $resolver
     */
    public function registerResolver(string $type, Closure $resolver): void
    {
        $this->resolvers[strtolower($type)] = $resolver;
    }

    /**
     * List registered relation types (for introspection/UI).
     *
     * @return array<int, string>
     */
    public function knownTypes(): array
    {
        return array_keys($this->resolvers);
    }

    /**
     * Whether the relation type is registered.
     */
    public function isRegistered(string $type): bool
    {
        return isset($this->resolvers[strtolower($type)]);
    }

    /**
     * Resolve an entity by type + id.
     *
     * @param string $type
     * @param int $id
     * @return array<string, mixed>  relation data (e.g. ['id'=>, 'name'=>, 'type'=>])
     * @throws RelationTypeNotRegisteredException if the type is not registered
     */
    public function resolve(string $type, int $id): array
    {
        $type = strtolower($type);

        if (!isset($this->resolvers[$type])) {
            throw new RelationTypeNotRegisteredException($type);
        }

        $data = ($this->resolvers[$type])($id);

        $payload = ['type' => $type, 'id' => $id, 'data' => $data];
        $resolving = new \Spine\Events\RelationResolving($payload);
        event($resolving);
        $payload = $resolving->payload;

        return $payload['data'];
    }
}
