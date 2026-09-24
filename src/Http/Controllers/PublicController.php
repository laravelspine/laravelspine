<?php

declare(strict_types=1);

namespace Spine\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class PublicController extends \Spine\Http\Controllers\Controller
{
    private const LANGUAGES = ['en', 'id', 'ko', 'ja', 'zh'];

    public function content(Request $request): JsonResponse
    {
        $lang = $this->resolveLang($request);
        $content = config('public_content', []);
        $data = $content[$lang] ?? $content['en'];

        // Apply translations to dynamic label fields
        $translated = $this->translateContent($data, $lang);

        return $this->ok($translated);
    }

    protected function resolveLang(Request $request): string
    {
        $lang = $request->query('lang');
        if ($lang && in_array($lang, self::LANGUAGES, true)) {
            return $lang;
        }

        // Fallback to Accept-Language header
        $accept = $request->header('Accept-Language', '');
        foreach (self::LANGUAGES as $l) {
            if (stripos($accept, $l) !== false) {
                return $l;
            }
        }

        return config('app.locale', 'en');
    }

    /**
     * Walk the content array and translate label fields using Lang::get().
     */
    protected function translateContent(array $data, string $lang): array
    {
        Lang::locale($lang);

        return $this->walkAndTranslate($data);
    }

    protected function walkAndTranslate(mixed $data): mixed
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                if (is_string($value) && $this->looksLikeTranslationKey($key)) {
                    $result[$key] = __($value);
                } elseif (is_array($value)) {
                    $result[$key] = $this->walkAndTranslate($value);
                } else {
                    $result[$key] = $value;
                }
            }

            return $result;
        }

        return $data;
    }

    protected function looksLikeTranslationKey(string $key): bool
    {
        // Keys that should be translated: title, subtitle, label, desc, text, message, etc.
        $translatable = ['title', 'subtitle', 'label', 'desc', 'description', 'text', 'message', 'body', 'button', 'tagline', 'copyright', 'brand'];

        foreach ($translatable as $t) {
            if (stripos($key, $t) !== false) {
                return true;
            }
        }

        return false;
    }
}
