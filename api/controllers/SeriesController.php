<?php

require_once __DIR__ . '/../services/SeriesService.php';

class SeriesController
{
    private SeriesService $service;

    public function __construct()
    {
        $this->service = new SeriesService();
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

    public function details(int $id): void
    {
        $result = $this->service->details($id);

        if (!$result['success']) {
            Response::error($result['message'] ?? 'Series not found', 404);
        }

        Response::success($result['data']);
    }
}
