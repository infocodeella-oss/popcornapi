<?php

require_once __DIR__ . '/../models/Supabase.php';

class RamadanController
{
    public function index(): void
    {

        $response = [
            Supabase::table('cafe_series')->all([
                'select'  => 'distinct(title), *',
                'order'  => 'id.desc',
                'section' => 'eq.' . rawurlencode('رمضان 2026'),
                'limit'  => 20
            ])['data']
        ];

        Response::success($response);
    }
}
