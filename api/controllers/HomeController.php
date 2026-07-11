<?php

require_once __DIR__ . '/../models/Supabase.php';

class HomeController
{
    public function index(): void
    {
        // $response = [
        //     'latest_movies' => Supabase::table('movies')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data'],

        //     'latest_series' => Supabase::table('series')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data'],

        //     'latest_dramacafe' => Supabase::table('dramacafe')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data'],

        //     'latest_turkish_series' => Supabase::table('turkish_series')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data'],

        //     'latest_arabic_movies' => Supabase::table('arabic_movies')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data'],

        //     'latest_cafe_series' => Supabase::table('cafe_series')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data'],

        //     'latest_plays' => Supabase::table('plays')->all([
        //         'select' => '*',
        //         'order'  => 'id.desc',
        //         'limit'  => 10
        //     ])['data']
        // ];


        $latest_added = array_merge(Supabase::table('movies')->all([
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => 2
        ])['data'],Supabase::table('series')->all([
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => 2
        ])['data']);

        $response = [
            'latest_added' => $latest_added
        ];

        Response::success($response);
    }
}