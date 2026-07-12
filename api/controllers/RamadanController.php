<?php

require_once __DIR__ . '/../models/Supabase.php';

class RamadanController
{
    public function index(): void
    {

        $response = [
            Supabase::table('v_ramadan_first_episodes')->all([
                'select'  => '*',
                'section' => 'eq.' . rawurlencode('رمضان 2026'),
                'limit'   => 20
            ])['data']
        ];

        Response::success($response);
    }
}
