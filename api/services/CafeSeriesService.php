<?php

require_once __DIR__ . '/../models/Supabase.php';

class CafeSeriesService
{
    private Supabase $series;

    public function __construct()
    {
        $this->series = Supabase::table('cafe_series');
    }

    public function getAll(): array
    {
        $params = [
            'select' => '*',
            'order'  => 'id.desc',
            'cover'  => 'neq.',
            'limit'  => Helpers::getLimit(),
            'offset' => Helpers::getOffset()
        ];

        if ($search = Helpers::getQuery('search')) {
            $params['title'] = 'ilike.*' . rawurlencode($search) . '*';
        }

        if ($section = Helpers::getQuery('section')) {
            $params['section'] = 'ilike.*' . rawurlencode($section);
        }

        if ($category = Helpers::getQuery('category')) {
            $params['category'] = 'ilike.*' . rawurlencode($category);
        }

        return $this->series->all($params);
    }

    public function find(int $id): array
    {
        return $this->series->find($id);
    }

    public function getEpisodes(string $title): array
    {
        return $this->series->where([
            'select' => '*',
            'title'  => 'ilike.*' . $title . '*',
            'order'  => 'id.asc'
        ]);
    }

    public function distinct(): array
    {
        $result = $this->series->all([
            'select' => '*',
            'order' => 'id.desc'
        ]);

        if (!$result['success']) {
            return $result;
        }

        $series = [];

        foreach ($result['data'] as $item) {

            $info = Helpers::parseCafeSeriesTitle($item['title']);

            $key = mb_strtolower(trim($info['series']));

            if (!isset($series[$key])) {

                $series[$key] = [

                    'id' => $item['id'],
                    'title' => $info['series'],
                    'cover' => $item['cover'],
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

            if ($info['season'] > $series[$key]['total_seasons']) {
                $series[$key]['total_seasons'] = $info['season'];
            }

            if (

                $info['season'] > $series[$key]['last_episode']['season']

                ||

                (

                    $info['season'] == $series[$key]['last_episode']['season']

                    &&

                    $info['episode'] > $series[$key]['last_episode']['episode']

                )

            ) {

                $series[$key]['last_episode'] = [

                    'season' => $info['season'],
                    'episode' => $info['episode']

                ];

                $series[$key]['latest_episode_id'] = $item['id'];
            }
        }

        usort($series, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        $limit = Helpers::getLimit();

        $page = max(1, (int)Helpers::getQuery('page', 1));

        $total = count($series);

        $offset = ($page - 1) * $limit;

        return [

            'success' => true,

            'data' => [

                'pagination' => [

                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)

                ],

                'results' => array_slice(array_values($series), $offset, $limit)

            ]

        ];
    }

    public function details(int $id): array
    {
        $current = $this->find($id);

        if (!$current['success'] || empty($current['data'])) {
            return [
                'success' => false,
                'message' => 'Series not found'
            ];
        }

        $currentEpisode = $current['data'][0];

        $parsed = Helpers::parseCafeSeriesTitle($currentEpisode['title']);

        $episodes = $this->series->where([
            'select' => '*',
            'title' => 'ilike.*' . rawurlencode($parsed['series']) . '*',
            'order' => 'id.asc'
        ]);

        if (!$episodes['success']) {
            return [
                'success' => false,
                'message' => 'Episodes not found'
            ];
        }

        $seasons = [];

        foreach ($episodes['data'] as $episode) {

            $info = Helpers::parseCafeSeriesTitle($episode['title']);

            $season = $info['season'];

            if (!isset($seasons[$season])) {

                $seasons[$season] = [
                    'season' => $season,
                    'episodes' => []
                ];
            }

            $episode['season'] = $info['season'];
            $episode['episode'] = $info['episode'];
            $episode['current'] = ($episode['id'] == $id);

            $seasons[$season]['episodes'][] = $episode;
        }

        ksort($seasons);

        foreach ($seasons as &$season) {

            usort($season['episodes'], function ($a, $b) {
                return $a['episode'] <=> $b['episode'];
            });
        }

        return [
            'success' => true,
            'data' => [
                'series' => [
                    'title' => $parsed['series'],
                    'cover' => $currentEpisode['cover']
                ],
                'current_episode' => [
                    'id' => $currentEpisode['id'],
                    'season' => $parsed['season'],
                    'episode' => $parsed['episode']
                ],
                'total_seasons' => count($seasons),
                'total_episodes' => count($episodes['data']),
                'seasons' => array_values($seasons)
            ]
        ];
    }
}
