<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect quick actions from core + modules.
 */
class QuickActions
{
    use Dispatchable;

    public array $items = [];

    public function addItem(array $item): void
    {
        $this->items[] = $item;
    }
}