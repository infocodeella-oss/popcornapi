<?php

require_once __DIR__ . '/../services/CafeSeriesService.php';

class CafeController
{
    private CafeSeriesService $service;

    public function __construct()
    {
        $this->service = new CafeSeriesService();
    }

    public function index(): void
    {
        $result = $this->service->getAll();

        if (!$result['success']) {
            Response::error('Failed to fetch series', $result['status']);
        }

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->service->find($id);

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
        $result = $this->service->distinct();

        if (!$result['success']) {
            Response::error('Failed to fetch series', 500);
        }

        Response::success($result['data']);
    }
}
