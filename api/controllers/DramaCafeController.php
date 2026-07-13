<?php

require_once __DIR__ . '/../models/Supabase.php';

class DramaCafeController
{
    private Supabase $dramacafe;

    public function __construct()
    {
        $this->dramacafe = Supabase::table('dramacafe');
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

        if ($type = Helpers::getQuery('type')) {
            $params['type'] = 'eq.' . rawurlencode($type);
        }

        if ($category = Helpers::getQuery('category')) {
            $params['category'] = 'ilike.*' . rawurlencode($category) . '*';
        }

        if ($dubbed = Helpers::getQuery('dubbed')) {
            $params['dubbed'] = 'eq.' . rawurlencode($dubbed);
        }

        $result = $this->dramacafe->all($params);

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->dramacafe->find($id);

        if (empty($result['data'])) {
            Response::notFound('Item not found');
        }

        Response::success($result['data'][0]);
    }

}
