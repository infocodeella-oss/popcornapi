<?php

require_once __DIR__ . '/../services/CafeSeriesService.php';
require_once __DIR__ . '/../models/Supabase.php';

class CafeController
{
    private CafeSeriesService $cafe;
    private Supabase $service;


    public function __construct()
    {
        $this->cafe = new CafeSeriesService();
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

        $result = $this->service->all($params);

        Response::success($result['data']);

    }

    public function show(int $id): void
    {
        $result = $this->cafe->find($id);

        if (!$result['success']) {
            Response::error('Failed to fetch series', $result['status']);
        }

        if (empty($result['data'])) {
            Response::notFound('Series not found');
        }

        Response::success($result['data']);
    }

    public function distinct(): void
    {
        $result = $this->cafe->distinct();

        if (!$result['success']) {
            Response::error('Failed to fetch series', 500);
        }

        Response::success($result['data']);
    }

    public function details(int $id): void
    {
        $result = $this->cafe->details($id);

        if (!$result['success']) {
            Response::error($result['message'] ?? 'Series not found', 404);
        }

        Response::success($result['data']);
    }
}
