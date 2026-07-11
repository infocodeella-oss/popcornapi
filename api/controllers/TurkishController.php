<?php

require_once __DIR__ . '/../models/Supabase.php';

class TurkishController
{
    private Supabase $turkish;

    public function __construct()
    {
        $this->turkish = Supabase::table('turkish_series');
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
            $params['series_name'] = 'ilike.*' . $search . '*';
        }

        if ($episode = Helpers::getQuery('episode')) {
            $params['episode_number'] = 'eq.' . $episode;
        }

        if ($isLast = Helpers::getQuery('is_last')) {
            $params['is_last'] = 'eq.' . strtolower($isLast);
        }

        $result = $this->turkish->all($params);

        Response::success($result['data']);
    }

    public function show(int $id): void
    {
        $result = $this->turkish->find($id);

        if (empty($result['data'])) {
            Response::notFound('Series not found');
        }

        Response::success($result['data'][0]);
    }
}