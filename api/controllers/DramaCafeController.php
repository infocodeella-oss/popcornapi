<?php

require_once __DIR__ . '/../services/DramaCafeService.php';

class DramaCafeController
{
    private DramaCafeService $service;

    public function __construct()
    {
        $this->service = new DramaCafeService();
    }

    public function index(): void
    {
        $result = $this->service->getAll();

        if (!$result['success']) {
            Response::error('Failed to fetch items', $result['status'] ?? 500);
        }

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->service->find($id);

        if (!$result['success']) {
            Response::error('Failed to fetch item', $result['status'] ?? 500);
        }

        if (empty($result['data'])) {
            Response::notFound('Item not found');
        }

        Response::success($result['data'][0]);
    }

    public function distinct(): void
    {
        $result = $this->service->distinct();

        if (!$result['success']) {
            Response::error(
                $result['message'] ?? 'Failed to fetch series',
                $result['status'] ?? 500
            );
        }

        Response::success($result['data']);
    }

    public function details(int $id): void
    {
        $result = $this->service->details($id);

        if (!$result['success']) {
            Response::error(
                $result['message'] ?? 'Series not found',
                $result['status'] ?? 404
            );
        }

        Response::success($result['data']);
    }
}