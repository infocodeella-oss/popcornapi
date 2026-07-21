<?php

date_default_timezone_set('Africa/Cairo');

define('APP_NAME', 'POPCORN API');
define('APP_VERSION', '1.0.0');

define('SUPABASE_DATABASES', [

    [
        "name" => "New",

        "url" => "https://uhdydbabctxrklbxptyk.supabase.co/rest/v1/",

        "key" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVoZHlkYmFiY3R4cmtsYnhwdHlrIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4NDEzMTY2NywiZXhwIjoyMDk5NzA3NjY3fQ.q2ze-7r6AcdVfOhXxicxn69oBfmfh0xUYnDBcCalbvU",

        "tables" => [
            "movies",
            "series",
            "dramacafe",
            "turkish_series",
        ]
    ],
    [
        "name" => "Main",

        "url" => "https://rlnowsoqwuqudybgyexz.supabase.co/rest/v1/",

        "key" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJsbm93c29xd3VxdWR5Ymd5ZXh6Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4MTcwMTMyMywiZXhwIjoyMDk3Mjc3MzIzfQ.mmxqDZCcilhEMdvnih7COPhd3-J9IP05BSJiAYvw0Qc",

        "tables" => [
            "movies",
            "series",
            "turkish_series",
        ]
    ],


]);

define('DEFAULT_LIMIT', 20);
define('MAX_LIMIT', 100);

define('JSON_OPTIONS', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, apikey');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
