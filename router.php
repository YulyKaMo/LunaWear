<?php
// Router para el servidor embebido de desarrollo PHP (php -S)
// Permite ejecutar PHP dentro de archivos .html localmente y servir estáticos con los tipos MIME correctos

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__ . '/public';

if ($uri === '/' || $uri === '') {
    $filePath = $publicDir . '/index.html';
} else {
    $filePath = $publicDir . $uri;
}

if (!file_exists($filePath)) {
    $rootFile = __DIR__ . $uri;
    if (file_exists($rootFile) && !is_dir($rootFile)) {
        $filePath = $rootFile;
    }
}

if (file_exists($filePath) && !is_dir($filePath)) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Si es un archivo .html, .htm o .php, procesarlo a través del motor PHP
    if (in_array($ext, ['html', 'htm', 'php'])) {
        chdir(dirname($filePath));
        require $filePath;
        return true;
    }

    // Tipos MIME garantizados para archivos estáticos
    $mimeTypes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'json'  => 'application/json; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'webp'  => 'image/webp',
        'svg'   => 'image/svg+xml',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf'
    ];

    if (isset($mimeTypes[$ext])) {
        header("Content-Type: {$mimeTypes[$ext]}");
        header("Content-Length: " . filesize($filePath));
        readfile($filePath);
        return true;
    }

    return false;
}

return false;
