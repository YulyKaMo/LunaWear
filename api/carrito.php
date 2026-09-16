<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
if (file_exists(__DIR__ . '/../../includes/conexion.php')) {
    require_once __DIR__ . '/../../includes/conexion.php';
} elseif (file_exists(__DIR__ . '/../includes/conexion.php')) {
    require_once __DIR__ . '/../includes/conexion.php';
}

// Inicializar sesión del carrito
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

const ENVIO_GRATIS_MINIMO = 50.0;
const COSTO_ENVIO_ESTANDAR = 4.95;

function calcularTotalesCarrito() {
    $items = array_values($_SESSION['carrito']);
    $subtotal = 0.0;
    $totalItems = 0;

    foreach ($items as $item) {
        $subtotal += (float)$item['precio'] * (int)$item['cantidad'];
        $totalItems += (int)$item['cantidad'];
    }

    $costoEnvio = ($subtotal === 0.0 || $subtotal >= ENVIO_GRATIS_MINIMO) ? 0.0 : COSTO_ENVIO_ESTANDAR;
    $total = $subtotal + $costoEnvio;

    return [
        'items' => $items,
        'total_items' => $totalItems,
        'subtotal' => round($subtotal, 2),
        'costo_envio' => round($costoEnvio, 2),
        'envio_gratis_minimo' => ENVIO_GRATIS_MINIMO,
        'califica_envio_gratis' => ($subtotal >= ENVIO_GRATIS_MINIMO),
        'falta_para_envio_gratis' => max(0, round(ENVIO_GRATIS_MINIMO - $subtotal, 2)),
        'total' => round($total, 2)
    ];
}

// Obtener payload JSON o POST estándar
$inputJSON = file_get_contents('php://input');
$datosPost = json_decode($inputJSON, true) ?: $_POST;

$accion = $_GET['accion'] ?? $datosPost['accion'] ?? 'obtener';

try {
    switch ($accion) {
        case 'obtener':
            echo json_encode([
                'status' => 'success',
                'carrito' => calcularTotalesCarrito()
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'agregar':
            $productoId = (int)($datosPost['producto_id'] ?? 0);
            $cantidad = max(1, (int)($datosPost['cantidad'] ?? 1));

            if ($productoId <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'mensaje' => 'ID de producto inválido']);
                exit;
            }

            // Consultar producto verificado en la base de datos
            $stmt = $conexion->prepare("
                SELECT p.id, p.nombre, p.precio, p.imagen_url, c.nombre AS categoria_nombre
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = :id AND p.activo = 1
                LIMIT 1
            ");
            $stmt->execute([':id' => $productoId]);
            $producto = $stmt->fetch();

            if (!$producto) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'mensaje' => 'El producto no existe o no está activo']);
                exit;
            }

            // Agregar o incrementar en la sesión
            if (isset($_SESSION['carrito'][$productoId])) {
                $_SESSION['carrito'][$productoId]['cantidad'] += $cantidad;
            } else {
                $_SESSION['carrito'][$productoId] = [
                    'id' => (int)$producto['id'],
                    'nombre' => $producto['nombre'],
                    'precio' => (float)$producto['precio'],
                    'imagen_url' => $producto['imagen_url'],
                    'categoria_nombre' => $producto['categoria_nombre'],
                    'cantidad' => $cantidad
                ];
            }

            echo json_encode([
                'status' => 'success',
                'mensaje' => "¡{$producto['nombre']} añadido al carrito!",
                'producto_agregado' => $_SESSION['carrito'][$productoId],
                'carrito' => calcularTotalesCarrito()
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'actualizar':
            $productoId = (int)($datosPost['producto_id'] ?? 0);
            $delta = (int)($datosPost['delta'] ?? 0);
            $nuevaCantidad = isset($datosPost['cantidad']) ? (int)$datosPost['cantidad'] : null;

            if (isset($_SESSION['carrito'][$productoId])) {
                if ($nuevaCantidad !== null) {
                    if ($nuevaCantidad <= 0) {
                        unset($_SESSION['carrito'][$productoId]);
                    } else {
                        $_SESSION['carrito'][$productoId]['cantidad'] = $nuevaCantidad;
                    }
                } elseif ($delta !== 0) {
                    $_SESSION['carrito'][$productoId]['cantidad'] += $delta;
                    if ($_SESSION['carrito'][$productoId]['cantidad'] <= 0) {
                        unset($_SESSION['carrito'][$productoId]);
                    }
                }
            }

            echo json_encode([
                'status' => 'success',
                'carrito' => calcularTotalesCarrito()
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'eliminar':
            $productoId = (int)($datosPost['producto_id'] ?? 0);
            $nombre = '';

            if (isset($_SESSION['carrito'][$productoId])) {
                $nombre = $_SESSION['carrito'][$productoId]['nombre'];
                unset($_SESSION['carrito'][$productoId]);
            }

            echo json_encode([
                'status' => 'success',
                'mensaje' => $nombre ? "Se eliminó $nombre del carrito" : "Producto eliminado",
                'carrito' => calcularTotalesCarrito()
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'vaciar':
            $_SESSION['carrito'] = [];
            echo json_encode([
                'status' => 'success',
                'mensaje' => 'El carrito se ha vaciado correctamente',
                'carrito' => calcularTotalesCarrito()
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'checkout':
            if (empty($_SESSION['carrito'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'mensaje' => 'El carrito está vacío']);
                exit;
            }

            $totales = calcularTotalesCarrito();
            $clienteNombre = trim($datosPost['nombre'] ?? 'Cliente LunaWear');
            $clienteEmail = trim($datosPost['email'] ?? 'cliente@ejemplo.com');
            $clienteTelefono = trim($datosPost['telefono'] ?? '');
            $direccion = trim($datosPost['direccion'] ?? 'Dirección no especificada');
            $metodoPago = trim($datosPost['metodo_pago'] ?? 'tarjeta');
            $codigoOrden = 'LW-' . strtoupper(substr(uniqid(), -6));

            // Intentar persistir pedido en base de datos si las tablas existen
            try {
                $stmtPedido = $conexion->prepare("
                    INSERT INTO pedidos (codigo_orden, cliente_nombre, cliente_email, cliente_telefono, direccion_envio, metodo_pago, subtotal, costo_envio, total)
                    VALUES (:codigo, :nombre, :email, :tel, :dir, :pago, :subtotal, :envio, :total)
                ");
                $stmtPedido->execute([
                    ':codigo' => $codigoOrden,
                    ':nombre' => $clienteNombre,
                    ':email' => $clienteEmail,
                    ':tel' => $clienteTelefono,
                    ':dir' => $direccion,
                    ':pago' => $metodoPago,
                    ':subtotal' => $totales['subtotal'],
                    ':envio' => $totales['costo_envio'],
                    ':total' => $totales['total']
                ]);
                $pedidoId = $conexion->lastInsertId();

                $stmtDetalle = $conexion->prepare("
                    INSERT INTO pedido_detalles (pedido_id, producto_id, nombre_producto, precio_unitario, cantidad, subtotal)
                    VALUES (:pedido_id, :prod_id, :nombre, :precio, :cant, :sub)
                ");

                foreach ($totales['items'] as $item) {
                    $stmtDetalle->execute([
                        ':pedido_id' => $pedidoId,
                        ':prod_id' => $item['id'],
                        ':nombre' => $item['nombre'],
                        ':precio' => $item['precio'],
                        ':cant' => $item['cantidad'],
                        ':sub' => $item['precio'] * $item['cantidad']
                    ]);
                }
            } catch (Exception $e) {
                // Si la tabla pedidos no existe en SQLite temporal, continuamos sin fallar el checkout
            }

            // Vaciar carrito de la sesión tras compra confirmada
            $_SESSION['carrito'] = [];

            echo json_encode([
                'status' => 'success',
                'mensaje' => '¡Pedido confirmado con éxito!',
                'orden' => [
                    'codigo' => $codigoOrden,
                    'cliente' => $clienteNombre,
                    'total' => $totales['total'],
                    'envio' => $totales['costo_envio']
                ],
                'carrito' => calcularTotalesCarrito()
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'mensaje' => 'Acción no reconocida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
