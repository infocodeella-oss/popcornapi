<?php

require_once __DIR__ . '/../models/Supabase.php';

class HomeController
{
    public function index(): void
    {

        $latest_added = array_merge(
            Supabase::table('movies')->all([
                'select' => '*',
                'order'  => 'id.desc',
                'limit'  => 4
            ])['data'],
            Supabase::table('series')->all([
                'select' => '*',
                'order'  => 'id.desc',
                'limit'  => 4
            ])['data'],
            Supabase::table('dramacafe')->all([
                'select' => '*',
                'order'  => 'id.desc',
                'limit'  => 4
            ])['data'],
            Supabase::table('turkish_series')->all([
                'select' => '*',
                'order'  => 'id.desc',
                'limit'  => 4
            ])['data']
        );

        $latest_arabic_series = Supabase::table('dramacafe')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'category' => 'eq.' . rawurlencode('مسلسلات عربية'),
            'limit'    => 20,
        ])['data'];

        $latest_english_series = array_merge(Supabase::table('series')->all([
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => 10
        ])['data'], Supabase::table('dramacafe')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'category' => 'eq.' . rawurlencode('مسلسلات اجنبي'),
            'limit'    => 10,
        ])['data']);

        $latest_turkish_series = array_merge(Supabase::table('dramacafe')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'category' => 'eq.' . rawurlencode('مسلسلات تركية'),
            'limit'    => 10,
        ])['data'], Supabase::table('turkish_series')->all([
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => 10
        ])['data']);

        $latest_korean_series = Supabase::table('series')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'category' => 'eq.' . rawurlencode('الكورية'),
            'limit'    => 20,
        ])['data'];

        $latest_arabic_movies = array_merge(Supabase::table('dramacafe')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'category' => 'eq.' . rawurlencode('افلام عربي'),
            'limit'    => 10,
        ])['data'], Supabase::table('arabic-movies')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'limit'    => 10,
        ])['data']);

        $latest_english_movies = array_merge(Supabase::table('movies')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'limit'    => 10,
        ])['data'], Supabase::table('dramacafe')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'category' => 'eq.' . rawurlencode('افلام اجنبي'),
            'limit'    => 10,
        ])['data']);

        $latest_french_movies = Supabase::table('movies')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'language' => 'eq.' . rawurlencode('الفرنسية'),
            'limit'    => 10,
        ])['data'];

        $latest_animation_movies = Supabase::table('movies')->all([
            'select'   => '*',
            'order'    => 'id.desc',
            'types' => 'ilike.*' . rawurlencode('كرتون') . '*',
            'limit'    => 10,
        ])['data'];

        $latest_plays = Supabase::table('plays')->all([
            'select' => '*',
            'order'  => 'id.desc',
            'limit'  => 10
        ])['data'];

        $response = [
            'latest_added' => $latest_added,
            'latest_arabic_series' => $latest_arabic_series,
            'latest_english_series' => $latest_english_series,
            'latest_turkish_series' => $latest_turkish_series,
            'latest_korean_series' => $latest_korean_series,
            'latest_arabic_movies' => $latest_arabic_movies,
            'latest_english_movies' => $latest_english_movies,
            'latest_french_movies' => $latest_french_movies,
            'latest_animation_movies' => $latest_animation_movies,
            'latest_plays' => $latest_plays,
        ];

        Response::success($response);
    }
}
