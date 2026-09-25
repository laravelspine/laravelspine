<?php

declare(strict_types=1);

namespace Spine\Support\Helpers;

/**
 * String helpers.
 *
 * Functions already provided by Laravel (Str::startsWith, Arr::flatten, etc.)
 * are not duplicated here; use the native Laravel ones directly.
 *
 * The functions below are not available in native Laravel and are still
 * needed by business logic.
 *
 */
class Str
{
    /**
     * Whether the string starts with the given substring.
     *
     * @deprecated Use Illuminate\Support\Str::startsWith() directly.
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return \Illuminate\Support\Str::startsWith($haystack, $needle);
    }

    /**
     * Whether the string ends with the given substring.
     *
     * @deprecated Use Illuminate\Support\Str::endsWith() directly.
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return \Illuminate\Support\Str::endsWith($haystack, $needle);
    }

    /**
     * Return the substring after the first occurrence of `needle`.
     *
     * Example: Str::strafter('foo-bar-baz', 'foo-') → 'bar-baz'
     * If the needle is not found, returns an empty string.
     */
    public static function strafter(string $haystack, string $needle): string
    {
        $pos = strpos($haystack, $needle);

        if ($pos === false) {
            return '';
        }

        return substr($haystack, $pos + strlen($needle));
    }

    /**
     * Return the substring before the first occurrence of `needle`.
     *
     * Example: Str::strbefore('foo-bar-baz', '-bar') → 'foo'
     * If the needle is not found, returns the original string.
     */
    public static function strbefore(string $haystack, string $needle): string
    {
        $pos = strpos($haystack, $needle);

        if ($pos === false) {
            return $haystack;
        }

        return substr($haystack, 0, $pos);
    }

    /**
     * Get the substring between two delimiters.
     *
     * Example: Str::get_string_between('foo[bar]baz', '[', ']') → 'bar'
     * If a delimiter is not found, returns null.
     */
    public static function get_string_between(string $input, string $start, string $end): ?string
    {
        $pos = strpos($input, $start);

        if ($pos === false) {
            return null;
        }

        $afterStart = substr($input, $pos + strlen($start));
        $endPos = strpos($afterStart, $end);

        if ($endPos === false) {
            return null;
        }

        return substr($afterStart, 0, $endPos);
    }

    /**
     * Slugify a string → lowercase, spaces/underscores to dashes,
     * strip non-alphanumeric characters (besides dashes), collapse double dashes.
     *
     * Example: Str::sluq_it('Hello  World!') → 'hello-world'
     */
    public static function sluq_it(string $str): string
    {
        // Lowercase and trim
        $str = strtolower(trim($str));

        // Strip non-alphanumeric characters, except spaces and dashes
        $str = preg_replace('/[^\w\s-]/u', '', $str);

        // Replace spaces and underscores with dashes
        $str = preg_replace('/[\s_]+/u', '-', $str);

        // Collapse repeated dashes and trim dashes at the ends
        $str = preg_replace('/-+/u', '-', $str);

        return trim($str, '-');
    }

    /**
     * Whether a value exists in a multidimensional array (recursive, loose comparison).
     *
     * @param mixed $needle The value to search for
     * @param array $haystack The array (possibly nested) to search in
     * @param bool $strict Use === (true) or == (false)
     */
    public static function in_array_multidimensional(mixed $needle, array $haystack, bool $strict = false): bool
    {
        foreach ($haystack as $item) {
            if (is_array($item)) {
                if (self::in_array_multidimensional($needle, $item, $strict)) {
                    return true;
                }
            } elseif ($strict ? $item === $needle : $item == $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Flatten a multidimensional array into one dimension (recursive, shallow-first).
     *
     * Unlike Arr::flatten, this keeps non-numeric string keys
     * (at the flattened level).
     */
    public static function array_flatten(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::array_flatten($value));
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert an (associative) array to a stdClass object.
     * An empty array yields null.
     */
    public static function array_to_object(array $array): ?object
    {
        if (empty($array)) {
            return null;
        }

        return json_decode(json_encode($array, JSON_THROW_ON_ERROR));
    }

    /**
     * Similarity of two strings in percent (0–100).
     *
     * Wrapper around PHP's similar_text().
     */
    public static function similarity(string $str1, string $str2): float
    {
        similar_text($str1, $str2, $percent);

        return (float) $percent;
    }
}
