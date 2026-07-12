<?php

require_once __DIR__ . '/../models/Supabase.php';

class MovieService
{
    private Supabase $movies;

    public function __construct()
    {
        $this->movies = Supabase::table('movies');
    }

    public function getAll(): array
    {
        $params = [
            'select' => '*',
            'order'  => 'id.desc',
            'cover'   => 'neq.',
            'limit'  => Helpers::getLimit(),
            'offset' => Helpers::getOffset()
        ];

        $search = Helpers::getQuery('search');

        if (!empty($search)) {
            $params['title'] = 'ilike.*' . rawurlencode($search) . '*';
        }

        if ($year = Helpers::getQuery('year')) {
            $params['year'] = 'eq.' . $year;
        }

        if ($language = Helpers::getQuery('language')) {
            $params['language'] = 'ilike.*' . rawurlencode($language);
        }

        if ($quality = Helpers::getQuery('quality')) {
            $params['quality'] = 'eq.' . rawurlencode($quality);
        }

        if ($category = Helpers::getQuery('category')) {
            $params['category'] = 'ilike.*' . rawurlencode($category) . '*';
        }

        if ($types = Helpers::getQuery('types')) {
            $params['types'] = 'ilike.*' . rawurlencode($types) . '*';
        }

        if ($actors = Helpers::getQuery('actors')) {
            $params['actors'] = 'ilike.*' . rawurlencode($actors) . '*';
        }

        if ($rate = Helpers::getQuery('rate')) {
            $params['rate'] = 'ilike.*' . rawurlencode($rate) . '*';
        }
        
        if ($section = Helpers::getQuery('section')) {
            $params['section'] = 'ilike.*' . rawurlencode($section);
        }

        return $this->movies->all($params);
    }

    public function find(int $id): array
    {
        return $this->movies->find($id);
    }
}