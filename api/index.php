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



if (empty($_REQUEST['want']) || !isset($_REQUEST['want'])) {
    echo json_encode(["result" => "Must Enter Want"], JSON_UNESCAPED_UNICODE);
    exit;
}

$want = $_REQUEST['want'];

// ========================================================================= //
// ================================= Statics =============================== //
// ========================================================================= //
if ($want == "statics") {
    
    $today_start = date("Y-m-d 00:00:00");
    $yesterday_start = date("Y-m-d 00:00:00", strtotime("-1 day"));
    $yesterday_end = date("Y-m-d 23:59:59", strtotime("-1 day"));

    $tables = ["movies", "series"];
    $final_report = [];
    $total_library = 0;

    foreach ($tables as $table) {
        
        $url_all = "https://" . SUPABASE_PROJECT_ID . ".supabase.co/rest/v1/" . $table . "?select=id";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_all);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: " . SUPABASE_TOKEN, "Authorization: Bearer " . SUPABASE_TOKEN, "Prefer: count=exact"]);
        $res_all = curl_exec($ch);
        curl_close($ch);
        
        $count_all = 0;
        if (preg_match('/Content-Range: .+\/(\d+)/i', $res_all, $matches)) {
            $count_all = (int)$matches[1];
        }
        $total_library += $count_all;
        $final_report["total_" . $table] = $count_all;

        $url_today = "https://" . SUPABASE_PROJECT_ID . ".supabase.co/rest/v1/" . $table . "?select=id&created_at=gte." . urlencode($today_start);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_today);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: " . SUPABASE_TOKEN, "Authorization: Bearer " . SUPABASE_TOKEN, "Prefer: count=exact"]);
        $res_today = curl_exec($ch);
        curl_close($ch);

        $count_today = 0;
        if (preg_match('/Content-Range: .+\/(\d+)/i', $res_today, $matches)) {
            $count_today = (int)$matches[1];
        }
        $final_report[$table . "_added_today"] = $count_today;
        $url_yesterday = "https://" . SUPABASE_PROJECT_ID . ".supabase.co/rest/v1/" . $table . "?select=id&created_at=gte." . urlencode($yesterday_start) . "&created_at=lte." . urlencode($yesterday_end);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_yesterday);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: " . SUPABASE_TOKEN, "Authorization: Bearer " . SUPABASE_TOKEN, "Prefer: count=exact"]);
        $res_yesterday = curl_exec($ch);
        curl_close($ch);

        $count_yesterday = 0;
        if (preg_match('/Content-Range: .+\/(\d+)/i', $res_yesterday, $matches)) {
            $count_yesterday = (int)$matches;
        }
        $final_report[$table . "_added_yesterday"] = $count_yesterday;
    }

    $output = [
        "summary" => [
            "total_library_items" => $total_library,
            "total_movies" => $final_report["total_movies"],
            "total_series_episodes" => $final_report["total_series"],
            "database_status" => "Healthy & Connected",
            "server_time" => date("Y-m-d H:i:s")
        ],
        "movies_activity" => [
            "total" => $final_report["total_movies"],
            "added_today" => $final_report["movies_added_today"],
            "added_yesterday" => $final_report["movies_added_yesterday"]
        ],
        "series_activity" => [
            "total" => $final_report["total_series"],
            "added_today" => $final_report["series_added_today"],
            "added_yesterday" => $final_report["series_added_yesterday"]
        ]
    ];

    echo json_encode(["result" => $output], JSON_UNESCAPED_UNICODE);
    exit;
}

// 7. جلب تفاصيل المسلسل والمواسم والحلقات بالكامل عن طريق معرف الحلقة (ID)
if ($want == "series_details") {
    $series_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    if ($series_id <= 0) {
        echo json_encode(["result" => "Valid Series ID Is Required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // الخطوة 1: جلب الحلقة المطلوبة لمعرفة اسم المسلسل الأصلي
    $single_episode = fetchFromSupabase("series", [
        "id" => "eq." . $series_id,
        "limit" => 1
    ]);

    if (empty($single_episode)) {
        echo json_encode(["result" => "Series or Episode Not Found"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $target_episode = $single_episode[0];
    $fullTitle = $target_episode['title'] ?? "";
    
    // استخراج اسم السلسلة الأساسي بدون الموسم والحلقة
    $clean_series_name = safeExtract($fullTitle, "مسلسل", "الموسم");
    if (empty($clean_series_name)) {
        $clean_series_name = $fullTitle;
    }

    // الخطوة 2: جلب كل الحلقات والمواسم التي تشترك في نفس اسم المسلسل
    $all_episodes = fetchFromSupabase("series", [
        "title" => "ilike.*" . trim($clean_series_name) . "*",
        "order" => "id.asc" // ترتيب تصاعدي من الحلقة الأولى للأحدث
    ]);

    // مصفوفة ذكية لتجميع المواسم والحلقات وتجنب التكرار
    $seasons_navigation = [];
    $series_metadata = [
        "series_title" => trim($clean_series_name),
        "cover" => $target_episode['cover'] ?? "",
        "story" => $target_episode['story'] ?? "",
        "actors" => $target_episode['actors'] ?? "",
        "types" => $target_episode['types'] ?? "",
        "country" => $target_episode['country'] ?? "غير معروف",
        "year" => isset($target_episode['year']) ? (int)$target_episode['year'] : 0,
        "rate" => isset($target_episode['rate']) ? (float)$target_episode['rate'] : 0.0,
    ];
        // الخطوة 3: فرز وترتيب الحلقات داخل المواسم ديناميكياً مع حماية المطابقة التامة
    foreach ($all_episodes as $row) {
        $rowFullTitle = $row['title'] ?? "";
        
        // 1. استخراج اسم المسلسل الحالي للحلقة لفحصه ومطابقته
        $current_series_title = safeExtract($rowFullTitle, "مسلسل", "الموسم");
        if(empty($current_series_title)) {
            $current_series_title = $rowFullTitle;
        }

        // 🌟 الشرط السحري: منع تداخل المسلسلات ذات الأسماء المتشابهة
        // إذا كان اسم المسلسل المستخرج لا يطابق الاسم المستهدف تماماً، يتم تخطي الحلقة فوراً
        if (trim($current_series_title) !== trim($clean_series_name)) {
            continue; 
        }
        
        // 2. استخراج اسم الموسم ورقم الحلقة باستخدام دالاتك المدمجة
        $seasonText = safeExtract($rowFullTitle, "الموسم", "الحلقة");
        $seasonNum = arabicTextToNumber(trim($seasonText)) ?: 1; // افتراضي 1 لو لم يُذكر
        
        $episodeRaw = safeExtract($rowFullTitle, "الحلقة", "مترجمة");
        $episodeNum = (int) filter_var($episodeRaw, FILTER_SANITIZE_NUMBER_INT);
        
        // إنشاء الموسم داخل مصفوفة التنقل لو لم يكن موجوداً مسبقاً
        if (!isset($seasons_navigation[$seasonNum])) {
            $seasons_navigation[$seasonNum] = [
                "season_number" => $seasonNum,
                "season_text" => "الموسم " . (empty($seasonText) ? $seasonNum : $seasonText),
                "episodes" => []
            ];
        }
        
        // إضافة الحلقة الحالية داخل مصفوفة الحلقات الخاصة بموسمها
        $seasons_navigation[$seasonNum]["episodes"][] = [
            "episode_id" => (int)$row['id'],
            "episode_number" => $episodeNum,
            "text" => numberToArabicText($episodeNum),
            "last" => str_contains($rowFullTitle, "والاخيرة"),
            "quality" => $row['quality'] ?? "",
            "downloads" => [
                "q1080" => $row['download_1080'] ?? "",
                "q720" => $row['download_720'] ?? "",
                "q480" => $row['download_480'] ?? "",
                "q240" => $row['download_240'] ?? "",
            ]
        ];
    }

    // إعادة ترتيب مصفوفة المواسم تصاعدياً (من الموسم الأول إلى الأحدث)
    ksort($seasons_navigation);
    
    // تحويل مصفوفة المواسم المفرزة إلى قائمة مرتبة متناسقة مع الـ JSON
    $series_metadata["seasons"] = array_values($seasons_navigation);

    echo json_encode(["result" => $series_metadata], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========================================================================= //
// ================================= MOVIES ================================ //
// ========================================================================= //

// إندبوينت ذكية لجلب بيانات صف واحد فقط بالـ ID للأفلام أو المسلسلات
if ($want == "get_by_id") {
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    $table = (isset($_REQUEST['type']) && $_REQUEST['type'] == 'series') ? 'series' : 'movies';

    if ($id <= 0) {
        echo json_encode(["result" => "Valid ID is required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase($table, ["id" => "eq." . $id, "limit" => 1]);

    if (!empty($data) && isset($data[0])) {
        echo json_encode(["status" => "success", "data" => $data[0]], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "result" => "Item not found"], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Home Page
if ($want == "home") {
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 1;

    if ($limit <= 0) {
        echo json_encode(["result" => "Should Enter Valid Limit"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $dataMovies = fetchFromSupabase("movies", [
        "select" => "*",
        "limit" => $limit,
        "cover"  => "neq.",
        "order" => "id.desc"
    ]);

    $result = [];
    $movies = [];
    $series = [];

    foreach ($dataMovies as $row) {
        $movies[] = [
            'id' => (int) $row['id'],
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
            ],
            "added_at" => $row['created_at']
        ];
    }

    
    $dataSeries = fetchFromSupabase("series", [
        "select" => "*",
        "limit" => $limit,
        "cover"  => "neq.",
        "order" => "id.desc"
    ]);

    foreach ($dataSeries as $row) {
        $fullTitle = $row['title'] ?? "";
        
        // تفكيك محتوى العنوان مثل المنطق القديم
        $seriesTitle = safeExtract($fullTitle, "مسلسل", "الموسم");
        if(empty($seriesTitle)) $seriesTitle = $fullTitle;

        $seasonText = safeExtract($fullTitle, "الموسم", "الحلقة");
        $episodeRaw = safeExtract($fullTitle, "الحلقة", "مترجمة");
        $episodeNum = (int) filter_var($episodeRaw, FILTER_SANITIZE_NUMBER_INT);

        $series[] = [
            'id' => (int) $row['id'],
            'title' => trim($seriesTitle),
            'season' => [
                "number" => arabicTextToNumber(trim($seasonText)),
                "text" => trim($seasonText),
            ],
            'episode' => [
                "number" => $episodeNum,
                "text" => numberToArabicText($episodeNum),
                "last" => str_contains($fullTitle, "والاخيرة")
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
            ],
            'added_at' => $row['created_at']
        ];
    }

    $result['movies'] = $movies;
    $result['series'] = $series;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);

    exit;
}

// Ramadan 2026
if ($want == "r2026") {
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 1;

    if ($limit <= 0) {
        echo json_encode(["result" => "Should Enter Valid Limit"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $dataMovies = fetchFromSupabase("movies", [
        "select" => "*",
        "limit" => $limit,
        "cover"  => "neq.",
        "order" => "id.desc"
    ]);

    $result = [];

    $r2026 = [];

    $searchWords = [
        "صحاب", "المداح", "سوق",
        "راس الافعى", "حكاية نرجس", "اتنين غيرنا",
        "بخمس ارواح", "وننسى اللي كان", "درش",
        "مسلسل فرصة اخيرة", "مسلسل علي كلاي", "الكينج",
        "الست موناليزا", "حد اقصى", "بابا وماما جيران",
        "بيبو", "اولاد الراعي", "على قد الحب",
        "المداح 6", "فن الحرب", "كلهم بيحبوا مودي",
        "ن النسوة", "فخر الدلتا", "قطر صغنطوط",
        "اللون الازرق", "هي كيميا", "سوا سوا",
        "النص التاني", "افراج", "أب ولكن"
    ]; 

    $addedIds = [];

    foreach ($searchWords as $word) {
        
        $searchResult = fetchFromSupabase("cafe_series", [
            "select" => "*",
            "title" => "ilike.*" . trim($word) . "*",
            "order" => "id.desc", 
            "limit" => 1
        ]);

        if (!empty($searchResult) && isset($searchResult[0])) {
            $row = $searchResult[0];
            
            if (!in_array($row['id'], $addedIds)) {
                $addedIds[] = $row['id'];
                
                $r2026[] = [
                    'id' => (int) $row['id'],
                    'title' => $row['title'],
                    'cover' => $row['cover'] ?? "",
                    'added_at' => $row['created_at']
                ];
            }
        }
    }

    $result['r2026'] = $r2026;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);

    exit;
}

// 1. جلب كل الأفلام بـ Limit محدد
if ($want == "movies") {
    $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

    if ($limit <= 0) {
        echo json_encode(["result" => "Should Enter Valid Limit"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("movies", [
        "select" => "*",
        "limit" => $limit,
        "cover"  => "neq.",
        "order" => "id.desc"
    ]);

    $result = [];
    foreach ($data as $row) {
        $result[] = [
            'id' => (int) $row['id'],
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
            ],
            "added_at" => $row['created_at']
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. البحث في الأفلام بالاسم
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
            'id' => (int) $row['id'],
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
            ],
            "added_at" => $row['created_at']
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

// Search Cafe
if ($want == 'search_cafe') {
    $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : "";
    $limit = isset($_REQUEST['limit']) ? trim($_REQUEST['limit']) : 20;

    if (empty($search)) {
        echo json_encode(["result" => "Search Text Is Required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = fetchFromSupabase("cafe_series", [
        "title" => "ilike.*" . $search . "*",
        "limit" => $limit
    ]);

    $result = [];
    foreach ($data as $row) {
        $result[] = [
            'id' => (int) $row['id'],
            'title' => trim($row['title']),
            'cover' => $row['cover'] ?? "",
            "download" => $row['download_480'] ?? "",
            "added_at" => $row['created_at']
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. فلترة الأفلام عن طريق التصنيف أو القسم
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
            'id' => (int) $row['id'],
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
            "added_at" => $row['created_at']
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}
// ========================================================================= //
// ================================= SERIES ================================ //
// ========================================================================= //

// 4. جلب كل المسلسلات بـ Limit محدد
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
        
        // تفكيك محتوى العنوان مثل المنطق القديم
        $seriesTitle = safeExtract($fullTitle, "مسلسل", "الموسم");
        if(empty($seriesTitle)) $seriesTitle = $fullTitle;

        $seasonText = safeExtract($fullTitle, "الموسم", "الحلقة");
        $episodeRaw = safeExtract($fullTitle, "الحلقة", "مترجمة");
        $episodeNum = (int) filter_var($episodeRaw, FILTER_SANITIZE_NUMBER_INT);

        $result[] = [
            'id' => (int) $row['id'],
            'title' => trim($seriesTitle),
            'season' => [
                "number" => arabicTextToNumber(trim($seasonText)),
                "text" => trim($seasonText),
            ],
            'episode' => [
                "number" => $episodeNum,
                "text" => numberToArabicText($episodeNum),
                "last" => str_contains($fullTitle, "والاخيرة")
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
            ],
            'added_at' => $row['created_at']
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
            'id' => (int) $row['id'],
            'title' => trim($seriesTitle),
            'season' => [
                "number" => arabicTextToNumber(trim($seasonText)),
                "text" => trim($seasonText),
            ],
            'episode' => [
                "number" => $episodeNum,
                "text" => numberToArabicText($episodeNum),
                "last" => str_contains($fullTitle, "والاخيرة")
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
            ],
            "added_at" => $row['created_at']
        ];
    }
    echo json_encode(["result" => $result], JSON_UNESCAPED_UNICODE);
    exit;
}


echo json_encode(["result" => "Invalid Command Query or Action Method"], JSON_UNESCAPED_UNICODE);


// ========================================================================= //
// ========================== INSERT & UPDATE ROUTES ======================= //
// ========================================================================= //

/**
 * دالة مساعدة لإرسال طلبات الإدخال (POST) والتعديل (PUT) إلى Supabase
 */
function sendToSupabaseAPI($endpoint, $method, $payload) {
    $url = "https://" . SUPABASE_PROJECT_ID . ".supabase.co/rest/v1/" . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: " . SUPABASE_TOKEN,
        "Authorization: Bearer " . SUPABASE_TOKEN,
        "Content-Type: application/json",
        "Prefer: return=minimal"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        "status_code" => $httpCode,
        "response" => json_decode($response, true) ?: $response
    ];
}

// 8. إضافة مسلسل جديد (POST) -> ?want=add_series
if ($want == "add_series" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // جلب البيانات الخام المرسلة بصيغة JSON
    $inputData = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    if (empty($inputData['title'])) {
        echo json_encode(["result" => "Title is required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $payload = [
        "title" => $inputData['title'] ?? null,
        "rate" => $inputData['rate'] ?? null,
        "story" => $inputData['story'] ?? null,
        "types" => $inputData['types'] ?? null,
        "quality" => $inputData['quality'] ?? null,
        "year" => $inputData['year'] ?? null,
        "language" => $inputData['language'] ?? null,
        "country" => $inputData['country'] ?? null,
        "actors" => $inputData['actors'] ?? null,
        "cover" => $inputData['cover'] ?? null,
        "download_1080" => $inputData['download_1080'] ?? null,
        "download_720" => $inputData['download_720'] ?? null,
        "download_480" => $inputData['download_480'] ?? null,
        "download_240" => $inputData['download_240'] ?? null,
        "episode_url" => $inputData['episode_url'] ?? null,
        "custome_cover" => $inputData['custome_cover'] ?? null,
        "custome_title_image" => $inputData['custome_title_image'] ?? null
    ];

    $res = sendToSupabaseAPI("series", "POST", $payload);

    if ($res['status_code'] >= 200 && $res['status_code'] < 300) {
        echo json_encode(["result" => "Series added successfully"], JSON_UNESCAPED_UNICODE);
    } elseif ($res['status_code'] == 409) {
        echo json_encode(["result" => "Error: This series episode already exists"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["result" => "Failed to add series", "error" => $res['response']], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 9. إضافة فيلم جديد (POST) -> ?want=add_movie
if ($want == "add_movie" && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    if (empty($inputData['title'])) {
        echo json_encode(["result" => "Title is required"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $payload = [
        "title" => $inputData['title'] ?? null,
        "rate" => $inputData['rate'] ?? null,
        "story" => $inputData['story'] ?? null,
        "types" => $inputData['types'] ?? null,
        "quality" => $inputData['quality'] ?? null,
        "duration" => $inputData['duration'] ?? null,
        "year" => $inputData['year'] ?? null,
        "language" => $inputData['language'] ?? null,
        "category" => $inputData['category'] ?? null,
        "actors" => $inputData['actors'] ?? null,
        "cover" => $inputData['cover'] ?? null,
        "download_1080" => $inputData['download_1080'] ?? null,
        "download_720" => $inputData['download_720'] ?? null,
        "download_480" => $inputData['download_480'] ?? null,
        "download_240" => $inputData['download_240'] ?? null,
        "movie_url" => $inputData['movie_url'] ?? null,
        "custome_cover" => $inputData['custome_cover'] ?? null,
        "custome_title_image" => $inputData['custome_title_image'] ?? null
    ];

    $res = sendToSupabaseAPI("movies", "POST", $payload);

    if ($res['status_code'] >= 200 && $res['status_code'] < 300) {
        echo json_encode(["result" => "Movie added successfully"], JSON_UNESCAPED_UNICODE);
    } elseif ($res['status_code'] == 409) {
        echo json_encode(["result" => "Error: This movie already exists"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["result" => "Failed to add movie", "error" => $res['response']], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 10. تعديل بيانات مسلسل (PUT / POST) -> ?want=update_series
if ($want == "update_series") {
    $inputData = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;
    $id = isset($inputData['id']) ? intval($inputData['id']) : 0;

    if ($id <= 0) {
        echo json_encode(["result" => "Valid ID is required for update"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $payload = [];
    $fields = ['title', 'rate', 'story', 'types', 'quality', 'year', 'language', 'country', 'actors', 'cover', 'download_1080', 'download_720', 'download_480', 'download_240', 'episode_url', 'custome_cover', 'custome_title_image'];
    
    // بناء مصفوفة البيانات ديناميكياً لتعديل الحقول المرسلة فقط دون تصفير الباقي
    foreach ($fields as $field) {
        if (isset($inputData[$field])) {
            $payload[$field] = $inputData[$field];
        }
    }

    $res = sendToSupabaseAPI("series?id=eq." . $id, "PATCH", $payload);

    if ($res['status_code'] >= 200 && $res['status_code'] < 300) {
        echo json_encode(["result" => "Series updated successfully"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["result" => "Failed to update series", "error" => $res['response']], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// 11. تعديل بيانات فيلم (PUT / POST) -> ?want=update_movie
if ($want == "update_movie") {
    $inputData = json_decode(file_get_contents('php://input'), true) ?: $_REQUEST;
    $id = isset($inputData['id']) ? intval($inputData['id']) : 0;

    if ($id <= 0) {
        echo json_encode(["result" => "Valid ID is required for update"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $payload = [];
    $fields = ['title', 'rate', 'story', 'types', 'quality', 'duration', 'year', 'language', 'category', 'actors', 'cover', 'download_1080', 'download_720', 'download_480', 'download_240', 'movie_url', 'custome_cover', 'custome_title_image'];
    
    foreach ($fields as $field) {
        if (isset($inputData[$field])) {
            $payload[$field] = $inputData[$field];
        }
    }

    $res = sendToSupabaseAPI("movies?id=eq." . $id, "PATCH", $payload);

    if ($res['status_code'] >= 200 && $res['status_code'] < 300) {
        echo json_encode(["result" => "Movie updated successfully"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["result" => "Failed to update movie", "error" => $res['response']], JSON_UNESCAPED_UNICODE);
    }
    exit;
}