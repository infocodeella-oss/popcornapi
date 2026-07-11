<?php

class Helpers
{
    public static function getQuery(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function getLimit(): int
    {
        $limit = (int) self::getQuery('limit', DEFAULT_LIMIT);

        if ($limit <= 0) {
            $limit = DEFAULT_LIMIT;
        }

        if ($limit > MAX_LIMIT) {
            $limit = MAX_LIMIT;
        }

        return $limit;
    }

    public static function getPage(): int
    {
        $page = (int) self::getQuery('page', 1);

        return $page > 0 ? $page : 1;
    }

    public static function getOffset(): int
    {
        return (self::getPage() - 1) * self::getLimit();
    }

    public static function clean(string $text): string
    {
        return trim(strip_tags($text));
    }

    public static function watchLink(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        return str_replace(
            'd/',
            'embed-',
            str_replace('_x', '.html', $url)
        );
    }

    public static function json($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function removeDuplicates(array $items, string $key): array
    {
        $result = [];
        $seen = [];

        foreach ($items as $item) {
            if (!isset($item[$key])) {
                continue;
            }

            if (isset($seen[$item[$key]])) {
                continue;
            }

            $seen[$item[$key]] = true;
            $result[] = $item;
        }

        return $result;
    }
}