<?php

require_once __DIR__ . '/../models/Supabase.php';

class NetflixController
{
    public function index(): void
    {

        $response = [
            Supabase::table('movies')->all([
                'select' => '*',
                'order'  => 'id.desc',
                'section' => 'eq.' . rawurlencode('Netflix'),
                'limit'  => 20
            ])['data']
        ];

        Response::success($response);
    }
}
