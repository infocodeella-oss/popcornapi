<?php

require_once __DIR__ . '/../services/SearchService.php';

class SearchController
{
    private SearchService $service;

    public function __construct()
    {
        $this->service = new SearchService();
    }

    public function index(): void
    {
        $query = trim(Helpers::getQuery('q', ''));

        if (empty($query)) {
            Response::error('Search query is required', 400);
        }

        $type = Helpers::getQuery('type', 'all');

        $result = $this->service->search($query, $type);

        if (!$result['success']) {
            Response::error('Search failed', 500);
        }

        Response::success([
            'query' => $query,
            'type' => $type,
            'total' => count($result['data']),
            'results' => $result['data']
        ]);
    }
}