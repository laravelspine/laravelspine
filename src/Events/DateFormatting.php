<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired while a date/datetime is being formatted.
 *
 * Dispatched from DateService::format() / formatDateTime() / toSql().
 * The payload array is MUTABLE — listeners may adjust format/value/sql, or
 * throw a ValidationException to abort. Covers the date-format filter use
 * cases (get_current_date_format, after_format_date, after_format_datetime,
 * before_sql_date_format, to_sql_date_formatted, available_date_formats).
 */
class DateFormatting
{
    use Dispatchable;

    public function __construct(
        public array $payload,
    ) {}
}
