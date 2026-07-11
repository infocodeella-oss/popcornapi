<?php

require_once __DIR__ . '/../services/MovieService.php';

class MoviesController
{
    private MovieService $service;

    public function __construct()
    {
        $this->service = new MovieService();
    }

    public function index(): void
    {
        $result = $this->service->getAll();

        if (!$result['success']) {
            Response::error('Failed to fetch movies', $result['status']);
        }

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->service->find($id);

        if (!$result['success']) {
            Response::error('Failed to fetch movie', $result['status']);
        }

        if (empty($result['data'])) {
            Response::notFound('Movie not found');
        }

        Response::success($result['data'][0]);
    }
}