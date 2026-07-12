<?php

require_once __DIR__ . '/../models/Supabase.php';

class SeriesService
{
    private Supabase $series;

    public function __construct()
    {
        $this->series = Supabase::table('series');
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

        if ($year = Helpers::getQuery('year')) {
            $params['year'] = 'eq.' . rawurlencode($year);
        }

        if ($language = Helpers::getQuery('language')) {
            $params['language'] = 'ilike.*' . rawurlencode($language);
        }

        if ($country = Helpers::getQuery('country')) {
            $params['country'] = 'ilike.*' . rawurlencode($country);
        }

        if ($rate = Helpers::getQuery('rate')) {
            $params['rate'] = 'ilike.*' . rawurlencode($rate);
        }

        if ($types = Helpers::getQuery('types')) {
            $params['types'] = 'ilike.*' . rawurlencode($types);
        }

        if ($actors = Helpers::getQuery('actors')) {
            $params['actors'] = 'ilike.*' . rawurlencode($actors);
        }

        if ($section = Helpers::getQuery('section')) {
            $params['section'] = 'ilike.*' . rawurlencode($section);
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

    $parsed = Helpers::parseSeriesTitle($currentEpisode['title']);

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

        $info = Helpers::parseSeriesTitle($episode['title']);

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
                'cover' => $currentEpisode['cover'],
                'story' => $currentEpisode['story'],
                'year' => $currentEpisode['year']
            ],
            'current_episode' => [
                'id' => $currentEpisode['id'],
                'season' => $parsed['season'],
                'episode' => $parsed['episode']
            ],
            'total_seasons' => count($seasons),
            'seasons' => array_values($seasons)
        ]
    ];
}
}