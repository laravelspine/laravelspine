<?php

declare(strict_types=1);

namespace Spine\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const ALLOWED = ['en', 'id', 'ko', 'ja', 'zh'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        // 1. URL prefix: /{lang}/...
        $path = ltrim($request->path(), '/');
        $segments = explode('/', $path);

        if (isset($segments[0]) && in_array($segments[0], self::ALLOWED, true)) {
            return $segments[0];
        }

        // 2. Session (persisted from previous visit)
        $sessionLocale = session('locale');
        if ($sessionLocale && in_array($sessionLocale, self::ALLOWED, true)) {
            return $sessionLocale;
        }

        // 3. Authenticated user preference
        if ($request->user() && $request->user()->language) {
            $userLang = strtolower($request->user()->language);
            if (in_array($userLang, self::ALLOWED, true)) {
                return $userLang;
            }
        }

        // 4. Accept-Language header
        $acceptLang = $request->header('Accept-Language', '');
        foreach (self::ALLOWED as $lang) {
            if (stripos($acceptLang, $lang) !== false) {
                return $lang;
            }
        }

        // 5. Config default
        $configLocale = config('app.locale', 'en');
        if (in_array($configLocale, self::ALLOWED, true)) {
            return $configLocale;
        }

        return 'en';
    }
}
