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

    public static function parseSeriesTitle(string $title): array
    {
        $title = trim($title);

        $seasonMap = [
            'الاول' => 1,
            'الأول' => 1,
            'الثاني' => 2,
            'الثالث' => 3,
            'الرابع' => 4,
            'الخامس' => 5,
            'السادس' => 6,
            'السابع' => 7,
            'الثامن' => 8,
            'التاسع' => 9,
            'العاشر' => 10
        ];

        $series = $title;
        $season = 1;
        $episode = 1;

        if (preg_match('/^مسلسل\s+(.*?)\s+الموسم\s+([^\s]+)\s+الحلقة\s+(\d+)/u', $title, $matches)) {

            $series = trim($matches[1]);

            $seasonWord = trim($matches[2]);

            if (isset($seasonMap[$seasonWord])) {
                $season = $seasonMap[$seasonWord];
            } elseif (is_numeric($seasonWord)) {
                $season = (int)$seasonWord;
            }

            $episode = (int)$matches[3];
        }

        return [
            'series' => $series,
            'season' => $season,
            'episode' => $episode
        ];
    }

    public static function parseCafeSeriesTitle(string $title): array
    {
        $season = 1;
        $episode = 1;
        $series = trim($title);

        $seasonMap = [
            'الاول' => 1,
            'الأول' => 1,
            'الثاني' => 2,
            'الثالث' => 3,
            'الرابع' => 4,
            'الخامس' => 5,
            'السادس' => 6,
            'السابع' => 7,
            'الثامن' => 8,
            'التاسع' => 9,
            'العاشر' => 10
        ];

        if (preg_match('/^مسلسل\s+(.*?)\s+الموسم\s+([^\s]+)\s+الحلقة\s+(\d+)/u', $title, $m)) {

            $series = trim($m[1]);

            $word = trim($m[2]);

            $season = $seasonMap[$word] ?? (is_numeric($word) ? (int)$word : 1);

            $episode = (int)$m[3];
        } elseif (preg_match('/^مسلسل\s+(.*?)\s+الحلقة\s+(\d+)/u', $title, $m)) {

            $series = trim($m[1]);

            $episode = (int)$m[2];

            $season = 1;
        }

        return [

            'series' => $series,
            'season' => $season,
            'episode' => $episode

        ];
    }
}
