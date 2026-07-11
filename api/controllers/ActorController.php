<?php

require_once __DIR__ . '/../services/ActorService.php';

class ActorController
{
    private ActorService $service;

    public function __construct()
    {
        $this->service = new ActorService();
    }

    public function show(string $name): void
    {
        $name = trim(urldecode($name));

        if (empty($name)) {
            Response::error('Actor name is required', 400);
        }

        $result = $this->service->getWorks($name);

        if (!$result['success']) {
            Response::error('Failed to fetch actor works', 500);
        }

        Response::success([
            'actor' => $name,
            'total' => count($result['data']),
            'results' => $result['data']
        ]);
    }
}