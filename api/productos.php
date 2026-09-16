<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
if (file_exists(__DIR__ . '/../../includes/conexion.php')) {
    require_once __DIR__ . '/../../includes/conexion.php';
} elseif (file_exists(__DIR__ . '/../includes/conexion.php')) {
    require_once __DIR__ . '/../includes/conexion.php';
}

// Filtros recibidos por GET
$filtroCategoria = isset($_GET['categoria']) ? strtolower(trim($_GET['categoria'])) : 'todos';
$filtroDestacados = isset($_GET['destacados']) && ($_GET['destacados'] === 'true' || $_GET['destacados'] === '1');
$filtroBuscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$filtroOrden = isset($_GET['orden']) ? strtolower(trim($_GET['orden'])) : 'destacados';

try {
    $sql = "
        SELECT 
            p.id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.imagen_url,
            p.etiqueta,
            p.destacado,
            p.activo,
            p.categoria_id,
            p.rating_rate,
            p.rating_count,
            c.nombre AS categoria_nombre,
            c.slug AS categoria_slug
        FROM productos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE p.activo = 1
    ";

    $params = [];

    // Filtro por categoría
    if ($filtroCategoria !== 'todos' && $filtroCategoria !== '') {
        $sql .= " AND (c.slug = :categoria OR LOWER(c.nombre) = :categoria)";
        $params[':categoria'] = $filtroCategoria;
    }

    // Filtro por destacados
    if ($filtroDestacados) {
        $sql .= " AND p.destacado = 1";
    }

    // Filtro de búsqueda
    if ($filtroBuscar !== '') {
        $sql .= " AND (p.nombre LIKE :buscar OR p.descripcion LIKE :buscar OR c.nombre LIKE :buscar)";
        $params[':buscar'] = '%' . $filtroBuscar . '%';
    }

    // Ordenamiento
    switch ($filtroOrden) {
        case 'precio-asc':
            $sql .= " ORDER BY p.precio ASC";
            break;
        case 'precio-desc':
            $sql .= " ORDER BY p.precio DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY p.rating_rate DESC, p.rating_count DESC";
            break;
        default:
            $sql .= " ORDER BY p.destacado DESC, p.id DESC";
            break;
    }

    $consulta = $conexion->prepare($sql);
    $consulta->execute($params);
    $filas = $consulta->fetchAll();

    // Formatear respuesta JSON estructurada
    $productos = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'precio' => (float) $row['precio'],
            'imagen_url' => $row['imagen_url'],
            'categoria' => $row['categoria_slug'],
            'categoria_slug' => $row['categoria_slug'],
            'categoria_nombre' => $row['categoria_nombre'],
            'etiqueta' => $row['etiqueta'],
            'destacado' => (int) $row['destacado'],
            'rating' => [
                'rate' => (float) ($row['rating_rate'] ?? 4.5),
                'count' => (int) ($row['rating_count'] ?? 50)
            ]
        ];
    }, $filas);

    echo json_encode([
        'status' => 'success',
        'total' => count($productos),
        'filtro' => [
            'categoria' => $filtroCategoria,
            'destacados' => $filtroDestacados,
            'buscar' => $filtroBuscar,
            'orden' => $filtroOrden
        ],
        'productos' => $productos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al consultar productos: ' . $e->getMessage(),
        'productos' => []
    ], JSON_UNESCAPED_UNICODE);
}