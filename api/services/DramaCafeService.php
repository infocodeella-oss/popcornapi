<?php

require_once __DIR__ . '/../models/Supabase.php';

class DramaCafeService
{
    private Supabase $drama;
    private Supabase $cafe;

    public function __construct()
    {
        $this->drama = Supabase::table('dramacafe');
        $this->cafe  = Supabase::table('cafe_series');
    }

    public function getAll(): array
    {
        $params = [
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => Helpers::getLimit(),
            'offset' => Helpers::getOffset()
        ];

        if ($search = Helpers::getQuery('search')) {
            $params['title'] = 'ilike.*' . rawurlencode($search) . '*';
        }

        if ($type = Helpers::getQuery('type')) {
            $params['type'] = 'eq.' . rawurlencode($type);
        }

        if ($category = Helpers::getQuery('category')) {
            $params['category'] = 'ilike.*' . rawurlencode($category) . '*';
        }

        if ($section = Helpers::getQuery('section')) {
            $params['section'] = 'ilike.*' . rawurlencode($section) . '*';
        }

        return $this->drama->all($params);
    }

    public function find(int $id): array
    {
        return $this->drama->find($id);
    }

    private function getDramaSeries(): array
    {
        return $this->drama->where([
            'select' => '*',
            'type'   => 'eq.series',
            'order'  => 'id.desc'
        ]);
    }

    private function getCafeSeries(): array
    {
        return $this->cafe->all([
            'select' => '*',
            'cover'  => 'neq.',
            'order'  => 'id.desc'
        ]);
    }

    public function distinct(): array
    {
        $drama = $this->getDramaSeries();
        $cafe = $this->getCafeSeries();

        if (!$drama['success']) {
            return $drama;
        }

        if (!$cafe['success']) {
            return $cafe;
        }

        $items = [];

        /*
    |--------------------------------------------------------------------------
    | DramaCafe
    |--------------------------------------------------------------------------
    */

        foreach ($drama['data'] as $row) {

            $items[] = [

                'id' => $row['id'],
                'source' => 'dramacafe',

                'series' => trim($row['series_name']),
                'season' => (int)$row['season'],
                'episode' => (int)$row['episode'],

                'cover' => $row['cover'],
                'title' => $row['title'],
                'year' => $row['year'],
                'category' => $row['category'],
                'translated' => $row['translated'],
                'dubbed' => $row['dubbed'],
                'section' => $row['section']

            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Cafe Series
    |--------------------------------------------------------------------------
    */

        foreach ($cafe['data'] as $row) {

            $info = Helpers::parseSeriesTitle($row['title']);

            $items[] = [

                'id' => $row['id'],
                'source' => 'cafe',

                'series' => trim($info['series']),
                'season' => (int)$info['season'],
                'episode' => (int)$info['episode'],

                'cover' => $row['cover'],
                'title' => $row['title'],
                'year' => null,
                'category' => null,
                'translated' => null,
                'dubbed' => null,
                'section' => $row['section']

            ];
        }

        $series = [];

        foreach ($items as $item) {

            $key = mb_strtolower(trim($item['series']));

            if (empty($key)) {
                continue;
            }

            if (!isset($series[$key])) {

                $series[$key] = [

                    'id' => $item['id'],
                    'source' => $item['source'],

                    'title' => $item['series'],
                    'cover' => $item['cover'],
                    'year' => $item['year'],
                    'category' => $item['category'],
                    'translated' => $item['translated'],
                    'dubbed' => $item['dubbed'],
                    'section' => $item['section'],

                    'latest_episode_id' => $item['id'],

                    'total_seasons' => 0,
                    'total_episodes' => 0,

                    'last_episode' => [
                        'season' => 0,
                        'episode' => 0
                    ]

                ];
            }

            $series[$key]['total_episodes']++;

            if ($item['season'] > $series[$key]['total_seasons']) {

                $series[$key]['total_seasons'] = $item['season'];
            }

            if (

                $item['season'] > $series[$key]['last_episode']['season']

                ||

                (

                    $item['season'] == $series[$key]['last_episode']['season']

                    &&

                    $item['episode'] > $series[$key]['last_episode']['episode']

                )

            ) {

                $series[$key]['last_episode'] = [

                    'season' => $item['season'],
                    'episode' => $item['episode']

                ];

                $series[$key]['latest_episode_id'] = $item['id'];
                $series[$key]['source'] = $item['source'];
            }
        }
        if ($search = Helpers::getQuery('search')) {

            $series = array_filter($series, function ($row) use ($search) {

                return mb_stripos($row['title'], $search) !== false;
            });
        }

        if ($section = Helpers::getQuery('section')) {

            $series = array_filter($series, function ($row) use ($section) {

                return mb_stripos($row['section'] ?? '', $section) !== false;
            });
        }
        usort($series, function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });

        $limit = Helpers::getLimit();
        $page = max(1, (int) Helpers::getQuery('page', 1));

        $total = count($series);

        $offset = ($page - 1) * $limit;

        $data = array_slice(array_values($series), $offset, $limit);

        return [
            'success' => true,
            'data' => [

                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit),
                    'has_next' => ($offset + $limit) < $total,
                    'has_previous' => $page > 1
                ],

                'results' => $data

            ]
        ];
    }

    public function details(int $id): array
    {
        $source = Helpers::getQuery('source', 'dramacafe');

        if ($source === 'cafe') {

            $current = $this->cafe->find($id);

            if (!$current['success'] || empty($current['data'])) {

                return [
                    'success' => false,
                    'message' => 'Episode not found'
                ];
            }

            $episode = $current['data'][0];

            $info = Helpers::parseSeriesTitle($episode['title']);

            $seriesName = $info['series'];
        } else {

            $current = $this->drama->find($id);

            if (!$current['success'] || empty($current['data'])) {

                return [
                    'success' => false,
                    'message' => 'Episode not found'
                ];
            }

            $episode = $current['data'][0];

            $seriesName = trim($episode['series_name']);
        }

        $drama = $this->getDramaSeries();
        $cafe = $this->getCafeSeries();

        $items = [];

        /*
    |--------------------------------------------------------------------------
    | DramaCafe
    |--------------------------------------------------------------------------
    */

        foreach ($drama['data'] as $row) {

            if (mb_strtolower(trim($row['series_name'])) != mb_strtolower($seriesName)) {
                continue;
            }

            $items[] = [

                'id' => $row['id'],
                'source' => 'dramacafe',

                'title' => $row['title'],

                'season' => (int)$row['season'],
                'episode' => (int)$row['episode'],

                'cover' => $row['cover'],
                'page_url' => $row['page_url'],

                'translated' => $row['translated'],
                'dubbed' => $row['dubbed'],

                'watch_links' => $row['watch_links'],
                'download_links' => $row['download_links']

            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Cafe Series
    |--------------------------------------------------------------------------
    */

        foreach ($cafe['data'] as $row) {

            $info = Helpers::parseSeriesTitle($row['title']);

            if (mb_strtolower(trim($info['series'])) != mb_strtolower($seriesName)) {
                continue;
            }

            $items[] = [

                'id' => $row['id'],
                'source' => 'cafe',

                'title' => $row['title'],

                'season' => $info['season'],
                'episode' => $info['episode'],

                'cover' => $row['cover'],
                'page_url' => $row['download_page'],

                'translated' => null,
                'dubbed' => null,

                'watch_links' => [],
                'download_links' => [

                    [
                        'quality' => '480p',
                        'url' => $row['download_480']
                    ],

                    [
                        'quality' => '360p',
                        'url' => $row['download_360']
                    ],

                    [
                        'quality' => 'Download',
                        'url' => $row['dwonload_link']
                    ]

                ]

            ];
        }

        usort($items, function ($a, $b) {

            if ($a['season'] == $b['season']) {

                return $a['episode'] <=> $b['episode'];
            }

            return $a['season'] <=> $b['season'];
        });

        $seasons = [];

        foreach ($items as $item) {

            $season = (int)$item['season'];

            if (!isset($seasons[$season])) {

                $seasons[$season] = [
                    'season' => $season,
                    'episodes' => []
                ];
            }

            $item['current'] = (
                $item['id'] == $id &&
                $item['source'] == $source
            );

            $seasons[$season]['episodes'][] = $item;
        }

        ksort($seasons);

        $currentEpisode = null;
        $previousEpisode = null;
        $nextEpisode = null;

        $flatEpisodes = [];

        foreach ($seasons as &$season) {

            usort($season['episodes'], function ($a, $b) {

                return $a['episode'] <=> $b['episode'];
            });

            foreach ($season['episodes'] as $episode) {

                $flatEpisodes[] = $episode;
            }
        }

        foreach ($flatEpisodes as $index => $episode) {

            if (
                $episode['id'] == $id &&
                $episode['source'] == $source
            ) {

                $currentEpisode = $episode;

                if (isset($flatEpisodes[$index - 1])) {
                    $previousEpisode = $flatEpisodes[$index - 1];
                }

                if (isset($flatEpisodes[$index + 1])) {
                    $nextEpisode = $flatEpisodes[$index + 1];
                }

                break;
            }
        }

        return [

            'success' => true,

            'data' => [

                'series' => [

                    'title' => $seriesName,
                    'cover' => $currentEpisode['cover'] ?? null

                ],

                'current_episode' => $currentEpisode,

                'previous_episode' => $previousEpisode,

                'next_episode' => $nextEpisode,

                'total_seasons' => count($seasons),

                'total_episodes' => count($flatEpisodes),

                'seasons' => array_values($seasons)

            ]

        ];
    }
}
