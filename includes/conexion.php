<?php
// Conexión dinámica a la Base de Datos (MySQL para XAMPP con fallback transparente a SQLite)

$envFile = __DIR__ . '/env.php';
$env = file_exists($envFile) ? require $envFile : [];

$conexion = null;

// 1. Intentar conectar a MySQL (XAMPP)
if (!empty($env['database']['host'])) {
    $db = $env['database'];
    $dsn = "mysql:host={$db['host']};port=" . ($db['port'] ?? 3306) . ";dbname={$db['dbname']};charset=" . ($db['charset'] ?? 'utf8mb4');
    try {
        $conexion = new PDO($dsn, $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 2
        ]);
    } catch (PDOException $e) {
        // MySQL no disponible en este momento, usaremos SQLite
        $conexion = null;
    }
}

// 2. Fallback garantizado a SQLite local
if ($conexion === null) {
    $sqlitePath = __DIR__ . '/../database/luna_wear.db';
    try {
        $conexion = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e) {
        die(json_encode([
            'status' => 'error',
            'mensaje' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ]));
    }
}