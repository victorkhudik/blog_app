<?php

require_once __DIR__ . '/config/bootstrap.php';

use App\Core\Router\Router;
function searchFile($directory, $searchName) {
    $results = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $searchName) {
            $results[] = $file->getPathname();
        }
    }

    return $results;
}

try {
    $router = new Router();
    foreach (searchFile(__DIR__ . '/app', 'routes.xml') as $file) {
        $router->loadRoutesFromXml($file);
    }

    $router->dispatch();
} catch (\Exception $e) {
    // Обработка ошибок
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}