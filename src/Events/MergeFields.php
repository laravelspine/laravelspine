<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect merge fields for email templates.
 *
 * Listener returns array of field definitions:
 *   ['name' => 'invoice_number', 'label' => 'Invoice Number', 'group' => 'invoice']
 *
 * Return value merged into merge field resolver.
 */
class MergeFields
{
    use Dispatchable;

    public array $fields = [];

    public function addField(array $field): void
    {
        $this->fields[] = $field;
    }
}