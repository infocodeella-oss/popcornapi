<?php

require_once __DIR__ . '/../models/Supabase.php';

class ArabicMoviesController
{
    private Supabase $movies;

    public function __construct()
    {
        $this->movies = Supabase::table('arabic_movies');
    }

    public function index(): void
    {
        $params = [
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => Helpers::getLimit(),
            'offset' => Helpers::getOffset()
        ];

        if ($search = Helpers::getQuery('search')) {
            $params['name'] = 'ilike.*' . $search . '*';
        }

        if ($year = Helpers::getQuery('year')) {
            $params['year'] = 'eq.' . $year;
        }

        $result = $this->movies->all($params);

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->movies->find($id);

        if (empty($result['data'])) {
            Response::notFound('Movie not found');
        }

        Response::success($result['data'][0]);
    }
}