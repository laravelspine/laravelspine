<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

/**
 * System utility API (app info, available languages).
 *
 * @group api/v1
 *     * @subgroup System
 */
class SystemController extends Controller
{
    /**
     * List the languages (locales) available in the application.
     *
     * Scans the `lang/` directory (Laravel 12 convention) and always includes
     * the default `app.locale`. If no lang folder exists, only the default
     * locale is returned.
     *
     * @authenticated
     *
     * @response scenario=success {
     *   "data": ["id", "en"]
     * }
     */
    public function languages(): JsonResponse
    {
        $locales = [];

        $langDir = base_path('lang');
        if (is_dir($langDir)) {
            foreach (File::directories($langDir) as $dir) {
                $locales[] = basename($dir);
            }
        }

        $default = (string) config('app.locale');
        if (! in_array($default, $locales, true)) {
            array_unshift($locales, $default);
        }

        return response()->json(['data' => array_values(array_unique($locales))]);
    }
}
