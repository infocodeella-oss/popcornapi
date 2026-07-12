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
            echo "Hello";
            $params['title'] = 'ilike.*' . $search . '*';
        }

        if ($year = Helpers::getQuery('year')) {
            $params['year'] = 'eq.' . $year;
        }

        if ($language = Helpers::getQuery('language')) {
            $params['language'] = 'ilike.*' . $language;
        }

        if ($quality = Helpers::getQuery('quality')) {
            $params['quality'] = 'eq.' . $quality;
        }

        if ($category = Helpers::getQuery('category')) {
            $params['category'] = 'ilike.*' . $category . '*';
        }

        return $this->movies->all($params);
    }

    public function find(int $id): array
    {
        return $this->movies->find($id);
    }
}