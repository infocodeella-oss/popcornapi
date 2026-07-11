<?php

require_once __DIR__ . '/../models/Supabase.php';

class ActorService
{
    private array $tables = [
        [
            'table' => 'movies',
            'type' => 'movie'
        ],
        [
            'table' => 'series',
            'type' => 'series'
        ],
        [
            'table' => 'dramacafe',
            'type' => 'dramacafe'
        ],
        [
            'table' => 'turkish_series',
            'type' => 'turkish-series'
        ]
    ];

    public function getWorks(string $actor): array
    {
        $results = [];

        foreach ($this->tables as $config) {

            $response = Supabase::table($config['table'])->all([
                'select' => '*',
                'actors' => 'ilike.*' . $actor . '*'
            ]);

            if (!$response['success']) {
                continue;
            }

            foreach ($response['data'] as $item) {

                $item['_type'] = $config['type'];
                $item['_table'] = $config['table'];

                $results[] = $item;
            }
        }

        usort($results, function ($a, $b) {

            $yearA = (int)($a['year'] ?? 0);
            $yearB = (int)($b['year'] ?? 0);

            return $yearB <=> $yearA;
        });

        return [
            'success' => true,
            'data' => $results
        ];
    }
}