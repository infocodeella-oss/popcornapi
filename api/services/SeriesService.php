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
}