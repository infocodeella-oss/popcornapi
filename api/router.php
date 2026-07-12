<?php

$method = $_SERVER['REQUEST_METHOD'];

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$segments = array_values(array_filter(explode('/', $path)));

// لو فيه api فى المسار احذفها
$apiIndex = array_search('api', $segments);

if ($apiIndex !== false) {
    $segments = array_slice($segments, $apiIndex + 1);
}

// لو فيه v1 احذفها
if (!empty($segments) && $segments[0] === 'v1') {
    array_shift($segments);
}

$resource = $segments[0] ?? 'home';
$id = $segments[1] ?? null;

switch ($resource) {

    case 'ramadan2026':
        require_once __DIR__ . '/controllers/RamadanController.php';
        $controller = new RamadanController();
        $controller->index();
        break;

    case 'home':
        require_once __DIR__ . '/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    case 'movies':
        require_once __DIR__ . '/controllers/MoviesController.php';
        $controller = new MoviesController();

        if ($id !== null) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;

    case 'series':
        require_once __DIR__ . '/controllers/SeriesController.php';
        $controller = new SeriesController();

        if (!$id) {
            $controller->index();
            break;
        }

        if ($id === 'distinct') {
            $controller->distinct();
            break;
        }

        if (isset($segments[2]) && $segments[2] === 'details') {
            $controller->details((int)$id);
            break;
        }

        $controller->show((int)$id);

        break;

    case 'dramacafe':
        require_once __DIR__ . '/controllers/DramaCafeController.php';
        $controller = new DramaCafeController();

        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;

    case 'turkish-series':
        require_once __DIR__ . '/controllers/TurkishController.php';
        $controller = new TurkishController();

        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;

    case 'arabic-movies':
        require_once __DIR__ . '/controllers/ArabicMoviesController.php';
        $controller = new ArabicMoviesController();

        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;

    case 'cafe-series':
        require_once __DIR__ . '/controllers/CafeController.php';
        $controller = new CafeController();

        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;

    case 'plays':
        require_once __DIR__ . '/controllers/PlaysController.php';
        $controller = new PlaysController();

        if ($id) {
            $controller->show($id);
        } else {
            $controller->index();
        }
        break;

    case 'search':
        require_once __DIR__ . '/controllers/SearchController.php';
        $controller = new SearchController();
        $controller->index();
        break;

    case 'actors':
        require_once __DIR__ . '/controllers/ActorController.php';
        $controller = new ActorController();

        if ($id) {
            $controller->show(urldecode($id));
        } else {
            Response::error('Actor name is required', 400);
        }
        break;

    default:
        Response::notFound('Endpoint not found');
        break;
}
