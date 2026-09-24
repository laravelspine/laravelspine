<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class TranslationController extends \Spine\Http\Controllers\Controller
{
    private const LANGUAGES = ['en', 'id', 'ko', 'ja', 'zh'];

    /**
     * GET /api/v1/translations/{locale}
     * Returns all translation keys for the given locale.
     */
    public function translations(Request $request, string $locale): JsonResponse
    {
        if (! in_array($locale, self::LANGUAGES, true)) {
            $locale = 'en';
        }

        Lang::locale($locale);

        // Load from lang/{locale}.json (app translations)
        $appTranslations = $this->loadAppTranslations($locale);

        // Also load public content labels as fallback
        $content = config('public_content', []);
        $contentData = $content[$locale] ?? $content['en'] ?? [];

        return $this->ok([
            'locale' => $locale,
            'translations' => $appTranslations,
            'content' => $contentData,
        ]);
    }

    /**
     * Load translation keys from lang/{locale}.json.
     */
    protected function loadAppTranslations(string $locale): array
    {
        $path = base_path("lang/{$locale}.json");

        if (! File::exists($path)) {
            return [];
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        return $data ?? [];
    }
}
