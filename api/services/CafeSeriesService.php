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

        return $this->series->all($params);
    }


    public function ramadan(): array
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

        $rawResponse = $this->series->all($params);
        $rows = $rawResponse['data'] ?? $rawResponse;

        $groupedSeries = [];

        foreach ($rows as $row) {
            $seriesName = trim(preg_replace('/\s+الحلقة\s+\d+.*$/ui', '', $row['title']));

            if (!isset($groupedSeries[$seriesName])) {
                $groupedSeries[$seriesName] = [
                    'series_name' => $seriesName,
                    'cover'       => $row['cover'],
                    'section'     => $row['section'],
                    'episodes'    => []
                ];
            }

            $groupedSeries[$seriesName]['episodes'][] = [
                'id'            => $row['id'],
                'title'         => $row['title'],
                'download_page' => $row['download_page'],
                'dwonload_link' => $row['dwonload_link'],
                'download_480'  => $row['download_480'],
                'download_360'  => $row['download_360'],
                'created_at'    => $row['created_at']
            ];
        }

        return array_values($groupedSeries);
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
}
