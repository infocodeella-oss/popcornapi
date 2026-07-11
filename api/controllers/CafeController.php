<?php

require_once __DIR__ . '/../models/Supabase.php';

class CafeController
{
    private Supabase $cafe;

    public function __construct()
    {
        $this->cafe = Supabase::table('cafe_series');
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

        $result = $this->cafe->all($params);

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->cafe->find($id);

        if (empty($result['data'])) {
            Response::notFound('Series not found');
        }

        Response::success($result['data'][0]);
    }
}