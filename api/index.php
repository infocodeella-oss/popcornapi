<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

define('SUPABASE_PROJECT_ID', 'rlnowsoqwuqudybgyexz');
define('SUPABASE_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJsbm93c29xd3VxdWR5Ymd5ZXh6Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4MTcwMTMyMywiZXhwIjoyMDk3Mjc3MzIzfQ.mmxqDZCcilhEMdvnih7COPhd3-J9IP05BSJiAYvw0Qc');

function numberToArabicText($number) {
    $ones = [
        1 => 'الأولى',
        2 => 'الثانية',
        3 => 'الثالثة',
        4 => 'الرابعة',
        5 => 'الخامسة',
        6 => 'السادسة',
        7 => 'السابعة',
        8 => 'الثامنة',
        9 => 'التاسعة',
        10 => 'العاشرة',
    ];

    if ($number <= 10) {
        return $ones[$number] ?? null;
    }
    return $number . '';
}

function arabicTextToNumber($text) {
    $numbers = [
        'الاول' => 1,
        'الأولى' => 1,
        'الثاني' => 2,
        'الثانية' => 2,
        'الثالث' => 3,
        'الثالثة' => 3,
        'الرابع' => 4,
        'الرابعة' => 4,
        'الخامس' => 5,
        'الخامسة' => 5,
        'السادس' => 6,
        'السادسة' => 6,
        'السابع' => 7,
        'السابعة' => 7,
        'الثامن' => 8,
        'الثامنة' => 8,
        'التاسع' => 9,
        'التاسعة' => 9,
        'العاشر' => 10,
        'العاشرة' => 10,
    ];
    return $numbers[$text] ?? null;
}

function fetchFromSupabase($endpoint, $queryParams = []) {
    $queryString = http_build_query($queryParams);
    $url = "https://" . SUPABASE_PROJECT_ID . ".supabase.co/rest/v1/" . $endpoint . ($queryString ? "?" . $queryString : "");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_TOKEN,
        "Authorization: Bearer " . SUPABASE_TOKEN,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true) ?: [];
    }
    return [];
}

function safeExtract($string, $start, $end) {
    if (!$string) return "";
    $parts = explode($start, $string);
    if (count($parts) > 1) {
        $subParts = explode($end, $parts[1]); // تم تصحيح أخذ الجزء الثاني بعد كلمة البداية
        return trim($subParts[0]); // تم تصحيح أخذ الجزء الأول قبل كلمة النهاية
    }
    return "";
}

<<<<<<< HEAD
=======


// الفحص الأولي للـ Request والتحقق من وجود حقل want
>>>>>>> 55426024133f732a7307b61418cca0bcd9b65f72
if (empty($_REQUEST['want']) || !isset($_REQUEST['want'])) {
    echo json_encode(["result" => "Must Enter Want"], JSON_UNESCAPED_UNICODE);
    exit;
}

$want = $_REQUEST['want'];

// ========================================================================= //
// ================================= MOVIES ================================ //
// ========================================================================= //

if ($want == "movies") {
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

    if ($limit <= 0) {
        echo json_encode(["result" => "Should Enter Valid Limit"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("movies", [
        "select" => "*",
        "limit" => $limit,
        "order" => "id.desc"
    ]);

    $result = [];
    foreach ($data as $row) {
        $result[] = [
            'title' => trim(str_replace(['فيلم', 'مترجم اون لاين - توب سينما'], '', $row['title'])),
            'cover' => $row['cover'] ?? "",
            'story' => $row['story'] ?? "",
            'types' => $row['types'] ?? "",
            'actors' => $row['actors'] ?? "",
            'duration' => $row['duration'] ?? "",
            'year' => isset($row['year']) ? (int)$row['year'] : 0,
            'quality' => $row['quality'] ?? "",
            'language' => $row['language'] ?? "",
            'rate' => isset($row['rate']) ? (float)$row['rate'] : 0.0,
            'downloads' => [
                "q1080" => $row['download_1080'] ?? "",
                "q720" => $row['download_720'] ?? "",
                "q480" => $row['download_480'] ?? "",
                "q240" => $row['download_240'] ?? "",
            ]
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($want == 'search_movies') {
    $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : "";

    if (empty($search)) {
        echo json_encode(["result" => "Search Text Is Required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("movies", [
        "title" => "ilike.*" . $search . "*",
        "limit" => 20
    ]);

    $result = [];
    foreach ($data as $row) {
        $result[] = [
            'title' => trim(str_replace(['فيلم', 'مترجم اون لاين - توب سينما'], '', $row['title'])),
            'cover' => $row['cover'] ?? "",
            'story' => $row['story'] ?? "",
            'types' => $row['types'] ?? "",
            'actors' => $row['actors'] ?? "",
            'duration' => $row['duration'] ?? "",
            'year' => isset($row['year']) ? (int)$row['year'] : 0,
            'quality' => $row['quality'] ?? "",
            'language' => $row['language'] ?? "",
            'rate' => isset($row['rate']) ? (float)$row['rate'] : 0.0,
            'downloads' => [
                "q1080" => $row['download_1080'] ?? "",
                "q720" => $row['download_720'] ?? "",
                "q480" => $row['download_480'] ?? "",
                "q240" => $row['download_240'] ?? "",
            ]
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($want == 'filter_movies') {
    $filter = isset($_REQUEST['filter']) ? trim($_REQUEST['filter']) : "";
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 20;

    if (empty($filter)) {
        echo json_encode(["result" => "filter Is Required Like This (افلام انمي)"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("movies", [
        "category" => "ilike.*" . $filter . "*",
        "limit" => $limit
    ]);

    $result = [];
    foreach ($data as $row) {
        $result[] = [
            'title' => trim(str_replace(['فيلم', 'مترجم اون لاين - توب سينما'], '', $row['title'])),
            'cover' => $row['cover'] ?? "",
            'story' => $row['story'] ?? "",
            'category' => $row['category'] ?? "",
            'types' => $row['types'] ?? "",
            'actors' => $row['actors'] ?? "",
            'duration' => $row['duration'] ?? "",
            'year' => isset($row['year']) ? (int)$row['year'] : 0,
            'quality' => $row['quality'] ?? "",
            'language' => $row['language'] ?? "",
            'rate' => isset($row['rate']) ? (float)$row['rate'] : 0.0,
            'downloads' => [
                "q1080" => $row['download_1080'] ?? "",
                "q720" => $row['download_720'] ?? "",
                "q480" => $row['download_480'] ?? "",
                "q240" => $row['download_240'] ?? "",
            ],
            'added_at' => $row['created_at']
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}
// ========================================================================= //
// ================================= SERIES ================================ //
// ========================================================================= //

if ($want == "series") {
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

    if ($limit <= 0) {
        echo json_encode(["result" => "Should Enter Valid Limit"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("series", [
        "select" => "*",
        "limit" => $limit,
        "order" => "id.desc"
    ]);

    $result = [];
    foreach ($data as $row) {
        $fullTitle = $row['title'] ?? "";
        
        $seriesTitle = safeExtract($fullTitle, "مسلسل", "الموسم");
        if(empty($seriesTitle)) $seriesTitle = $fullTitle;

        $seasonText = safeExtract($fullTitle, "الموسم", "الحلقة");
        $episodeRaw = safeExtract($fullTitle, "الحلقة", "مترجمة");
        $episodeNum = (int) filter_var($episodeRaw, FILTER_SANITIZE_NUMBER_INT);

        $result[] = [
            'title' => trim($seriesTitle),
            'season' => [
                "number" => arabicTextToNumber(trim($seasonText)),
                "text" => trim($seasonText),
            ],
            'episode' => [
                "number" => $episodeNum,
                "text" => numberToArabicText($episodeNum)
            ],
            'cover' => $row['cover'] ?? "",
            'story' => $row['story'] ?? "",
            'types' => $row['types'] ?? "",
            'actors' => $row['actors'] ?? "",
            'country' => $row['country'] ?? "غير معروف",
            'year' => isset($row['year']) ? (int)$row['year'] : 0,
            'quality' => $row['quality'] ?? "",
            'rate' => isset($row['rate']) ? (float)$row['rate'] : 0.0,
            'downloads' => [
                "q1080" => $row['download_1080'] ?? "",
                "q720" => $row['download_720'] ?? "",
                "q480" => $row['download_480'] ?? "",
                "q240" => $row['download_240'] ?? "",
            ]
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($want == 'search_series') {
    $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : "";
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 20;

    if (empty($search)) {
        echo json_encode(["result" => "Search Text Is Required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("series", [
        "title" => "ilike.*" . $search . "*",
        "limit" => $limit
    ]);

    $result = [];
    foreach ($data as $row) {
        $fullTitle = $row['title'] ?? "";
        
        $seriesTitle = safeExtract($fullTitle, "مسلسل", "الموسم");
        if(empty($seriesTitle)) $seriesTitle = $fullTitle;

        $seasonText = safeExtract($fullTitle, "الموسم", "الحلقة");
        $episodeRaw = safeExtract($fullTitle, "الحلقة", "مترجمة");
        // تأمين جلب الأرقام فقط من النص المستخرج
        $episodeNum = !empty($episodeRaw) ? (int) filter_var($episodeRaw, FILTER_SANITIZE_NUMBER_INT) : 0; 

        $result[] = [
            'title' => trim($seriesTitle),
            'season' => [
                "number" => arabicTextToNumber(trim($seasonText)),
                "text" => trim($seasonText),
            ],
            'episode' => [
                "number" => $episodeNum,
                "text" => numberToArabicText($episodeNum)
            ],
            'cover' => $row['cover'] ?? "",
            'story' => $row['story'] ?? "",
            'types' => $row['types'] ?? "",
            'actors' => $row['actors'] ?? "",
            'country' => $row['country'] ?? "غير معروف",
            'year' => isset($row['year']) ? (int)$row['year'] : 0,
            'quality' => $row['quality'] ?? "",
            'rate' => isset($row['rate']) ? (float)$row['rate'] : 0.0,
            'downloads' => [
                "q1080" => $row['download_1080'] ?? "",
                "q720" => $row['download_720'] ?? "",
                "q480" => $row['download_480'] ?? "",
                "q240" => $row['download_240'] ?? "",
            ]
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(["result" => "Invalid Command Query or Action Method"], JSON_UNESCAPED_UNICODE);
