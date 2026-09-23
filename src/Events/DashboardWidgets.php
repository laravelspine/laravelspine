<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect dashboard widgets from core + modules.
 */
class DashboardWidgets
{
    use Dispatchable;

    public array $widgets = [];

    public function addWidget(array $widget): void
    {
        $this->widgets[] = $widget;
    }
}