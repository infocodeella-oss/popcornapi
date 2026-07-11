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
            $params['title'] = 'ilike.*' . $search . '*';
        }

        if ($year = Helpers::getQuery('year')) {
            $params['year'] = 'eq.' . $year;
        }

        if ($language = Helpers::getQuery('language')) {
            $params['language'] = 'eq.' . $language;
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