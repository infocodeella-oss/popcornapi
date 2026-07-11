<?php

require_once __DIR__ . '/../models/Supabase.php';

class SearchService
{
    private array $tables = [
        'movies' => [
            'table' => 'movies',
            'field' => 'title'
        ],
        'series' => [
            'table' => 'series',
            'field' => 'title'
        ],
        'dramacafe' => [
            'table' => 'dramacafe',
            'field' => 'title'
        ],
        'turkish-series' => [
            'table' => 'turkish_series',
            'field' => 'series_name'
        ],
        'arabic-movies' => [
            'table' => 'arabic_movies',
            'field' => 'name'
        ],
        'cafe-series' => [
            'table' => 'cafe_series',
            'field' => 'title'
        ],
        'plays' => [
            'table' => 'plays',
            'field' => 'title'
        ]
    ];

    public function search(string $query, string $type = 'all'): array
    {
        $results = [];

        foreach ($this->tables as $key => $config) {

            if ($type !== 'all' && $type !== $key) {
                continue;
            }

            $response = Supabase::table($config['table'])->all([
                'select' => '*',
                $config['field'] => 'ilike.*' . $query . '*',
                'limit' => Helpers::getLimit()
            ]);

            if (!$response['success']) {
                continue;
            }

            foreach ($response['data'] as $item) {

                $item['_type'] = $key;
                $item['_table'] = $config['table'];

                $results[] = $item;
            }
        }

        usort($results, function ($a, $b) {

            $aTitle = strtolower($a['title'] ?? $a['name'] ?? $a['series_name'] ?? '');
            $bTitle = strtolower($b['title'] ?? $b['name'] ?? $b['series_name'] ?? '');

            return strcmp($aTitle, $bTitle);
        });

        return [
            'success' => true,
            'data' => $results
        ];
    }
}