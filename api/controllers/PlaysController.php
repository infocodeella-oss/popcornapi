<?php

require_once __DIR__ . '/../models/Supabase.php';

class PlaysController
{
    private Supabase $plays;

    public function __construct()
    {
        $this->plays = Supabase::table('plays');
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
            $params['title'] = 'ilike.*' . $search . '*';
        }

        if ($year = Helpers::getQuery('year')) {
            $params['year'] = 'eq.' . $year;
        }

        $result = $this->plays->all($params);

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->plays->find($id);

        if (empty($result['data'])) {
            Response::notFound('Play not found');
        }

        Response::success($result['data'][0]);
    }
}