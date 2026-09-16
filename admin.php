<?php

require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/csrf.php';

function e($valor)
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$error = '';
$productoEditar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido.';
    } else {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear' || $accion === 'editar') {
            $id = $_POST['id'] ?? null;
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = (float) ($_POST['precio'] ?? 0);
            $imagenUrl = trim($_POST['imagen_url'] ?? '');
            $etiqueta = trim($_POST['etiqueta'] ?? '');
            $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
            $destacado = isset($_POST['destacado']) ? 1 : 0;

            if ($nombre === '' || $descripcion === '' || $precio <= 0 || $imagenUrl === '' || $categoriaId <= 0) {
                $error = 'Completa todos los campos obligatorios.';
            } else {
                if ($accion === 'crear') {
                    $sql = "
                        INSERT INTO productos (
                            nombre, descripcion, precio, imagen_url, etiqueta, destacado, activo, categoria_id
                        ) VALUES (
                            :nombre, :descripcion, :precio, :imagen_url, :etiqueta, :destacado, 1, :categoria_id
                        )
                    ";

                    $consulta = $conexion->prepare($sql);
                    $consulta->execute([
                        ':nombre' => $nombre,
                        ':descripcion' => $descripcion,
                        ':precio' => $precio,
                        ':imagen_url' => $imagenUrl,
                        ':etiqueta' => $etiqueta,
                        ':destacado' => $destacado,
                        ':categoria_id' => $categoriaId
                    ]);

                    $mensaje = 'Producto creado correctamente.';
                }

                if ($accion === 'editar') {
                    $sql = "
                        UPDATE productos
                        SET
                            nombre = :nombre,
                            descripcion = :descripcion,
                            precio = :precio,
                            imagen_url = :imagen_url,
                            etiqueta = :etiqueta,
                            destacado = :destacado,
                            categoria_id = :categoria_id,
                            actualizado_en = CURRENT_TIMESTAMP
                        WHERE id = :id
                    ";

                    $consulta = $conexion->prepare($sql);
                    $consulta->execute([
                        ':nombre' => $nombre,
                        ':descripcion' => $descripcion,
                        ':precio' => $precio,
                        ':imagen_url' => $imagenUrl,
                        ':etiqueta' => $etiqueta,
                        ':destacado' => $destacado,
                        ':categoria_id' => $categoriaId,
                        ':id' => $id
                    ]);

                    $mensaje = 'Producto actualizado correctamente.';
                }
            }
        }

        if ($accion === 'eliminar') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($id > 0) {
                $consulta = $conexion->prepare("UPDATE productos SET activo = 0 WHERE id = :id");
                $consulta->execute([':id' => $id]);

                $mensaje = 'Producto eliminado correctamente.';
            }
        }
    }
}

if (isset($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];

    $consulta = $conexion->prepare("SELECT * FROM productos WHERE id = :id LIMIT 1");
    $consulta->execute([':id' => $idEditar]);

    $productoEditar = $consulta->fetch();
}

$categorias = $conexion->query("
    SELECT id, nombre 
    FROM categorias 
    WHERE activo = 1 
    ORDER BY id ASC
")->fetchAll();

$productos = $conexion->query("
    SELECT 
        productos.*,
        categorias.nombre AS categoria
    FROM productos
    INNER JOIN categorias ON productos.categoria_id = categorias.id
    WHERE productos.activo = 1
    ORDER BY productos.id DESC
")->fetchAll();

$modoEditar = $productoEditar !== false && $productoEditar !== null;
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Admin | Luna Wear</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/styles.css"/>
</head>

<body>
  <header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm" data-bs-theme="dark">
      <div class="container">
        <a href="index.php" class="navbar-brand header__logo">
          Luna<span class="header__logo-accent">Wear</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin"
          aria-controls="navbarAdmin" aria-expanded="false" aria-label="Abrir menú">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAdmin">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a href="index.php" class="nav-link header__link">Inicio</a>
            </li>

            <li class="nav-item">
              <a href="index.php#catalogo" class="nav-link header__link">Catálogo</a>
            </li>

            <li class="nav-item">
              <a href="contact.html" class="nav-link header__link">Contacto</a>
            </li>

            <li class="nav-item">
              <a href="admin.php" class="nav-link header__link header__link--active">Admin</a>
            </li>

            <li class="nav-item">
              <a href="logout.php" class="nav-link header__link">Cerrar sesión</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <main class="admin-page">
    <section class="admin-hero">
      <div class="container">
        <span class="admin-hero__label">Panel de gestión</span>

        <h1 class="admin-hero__title">
          Administrar productos
        </h1>

        <p class="admin-hero__description">
          Crea, edita, elimina y marca productos como destacados para mostrarlos en la tienda.
        </p>
      </div>
    </section>

    <section class="admin">
      <div class="container">

        <?php if ($mensaje): ?>
          <div class="alert alert-success"><?= e($mensaje); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error); ?></div>
        <?php endif; ?>

        <div class="admin__grid">

          <div class="admin__form-card">
            <h2 class="admin__title">
              <?= $modoEditar ? 'Editar producto' : 'Crear producto'; ?>
            </h2>

            <p class="admin__description">
              Completa la información del producto que quieres mostrar en la tienda.
            </p>

            <form class="admin-form" method="POST">
              <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
              <input type="hidden" name="accion" value="<?= $modoEditar ? 'editar' : 'crear'; ?>">
              <input type="hidden" name="id" value="<?= $modoEditar ? e($productoEditar['id']) : ''; ?>">

              <div class="admin-form__group">
                <label for="nombre" class="admin-form__label">Nombre del producto</label>
                <input 
                  type="text" 
                  id="nombre" 
                  name="nombre"
                  class="admin-form__input" 
                  placeholder="Ej: Abrigo Camel" 
                  value="<?= $modoEditar ? e($productoEditar['nombre']) : ''; ?>"
                  required 
                />
              </div>

              <div class="admin-form__group">
                <label for="descripcion" class="admin-form__label">Descripción</label>
                <textarea 
                  id="descripcion" 
                  name="descripcion"
                  class="admin-form__textarea" 
                  rows="4"
                  placeholder="Descripción breve del producto" 
                  required
                ><?= $modoEditar ? e($productoEditar['descripcion']) : ''; ?></textarea>
              </div>

              <div class="admin-form__row">
                <div class="admin-form__group">
                  <label for="precio" class="admin-form__label">Precio</label>
                  <input 
                    type="number" 
                    id="precio" 
                    name="precio"
                    class="admin-form__input" 
                    min="0" 
                    step="0.01" 
                    placeholder="49.90"
                    value="<?= $modoEditar ? e($productoEditar['precio']) : ''; ?>"
                    required 
                  />
                </div>

                <div class="admin-form__group">
                  <label for="etiqueta" class="admin-form__label">Etiqueta</label>
                  <input 
                    type="text" 
                    id="etiqueta" 
                    name="etiqueta"
                    class="admin-form__input" 
                    placeholder="Nuevo, Premium, Oferta..."
                    value="<?= $modoEditar ? e($productoEditar['etiqueta']) : ''; ?>"
                  />
                </div>
              </div>

              <div class="admin-form__group">
                <label for="imagen_url" class="admin-form__label">URL de imagen</label>
                <input 
                  type="url" 
                  id="imagen_url" 
                  name="imagen_url"
                  class="admin-form__input" 
                  placeholder="https://..." 
                  value="<?= $modoEditar ? e($productoEditar['imagen_url']) : ''; ?>"
                  required 
                />
              </div>

              <div class="admin-form__group">
                <label for="categoria_id" class="admin-form__label">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="admin-form__select" required>
                  <option value="">Selecciona una categoría</option>

                  <?php foreach ($categorias as $categoria): ?>
                    <option 
                      value="<?= e($categoria['id']); ?>"
                      <?= $modoEditar && $productoEditar['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>
                    >
                      <?= e($categoria['nombre']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="admin-form__check">
                <input 
                  type="checkbox" 
                  id="destacado" 
                  name="destacado"
                  class="admin-form__checkbox"
                  <?= $modoEditar && (int)$productoEditar['destacado'] === 1 ? 'checked' : ''; ?>
                />
                <label for="destacado" class="admin-form__check-label">
                  Marcar como producto destacado
                </label>
              </div>

              <div class="admin-form__actions">
                <button type="submit" class="admin-form__button admin-form__button--primary">
                  <?= $modoEditar ? 'Actualizar producto' : 'Guardar producto'; ?>
                </button>

                <?php if ($modoEditar): ?>
                  <a href="admin.php" class="admin-form__button admin-form__button--secondary">
                    Cancelar
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>

          <div class="admin__list-card">
            <div class="admin__list-header">
              <div>
                <h2 class="admin__title">
                  Productos registrados
                </h2>

                <p class="admin__description">
                  Gestiona los productos visibles en la tienda.
                </p>
              </div>
            </div>

            <div class="admin__table-wrapper">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Destacado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($productos as $producto): ?>
                    <tr>
                      <td>
                        <div class="admin-table__product">
                          <img 
                            src="<?= e($producto['imagen_url']); ?>" 
                            alt="<?= e($producto['nombre']); ?>" 
                            class="admin-table__image"
                          >

                          <div>
                            <strong><?= e($producto['nombre']); ?></strong>
                            <span><?= e($producto['etiqueta']); ?></span>
                          </div>
                        </div>
                      </td>

                      <td><?= e($producto['categoria']); ?></td>

                      <td>€<?= number_format((float)$producto['precio'], 2); ?></td>

                      <td>
                        <span class="admin-table__badge <?= (int)$producto['destacado'] === 1 ? 'admin-table__badge--active' : ''; ?>">
                          <?= (int)$producto['destacado'] === 1 ? 'Sí' : 'No'; ?>
                        </span>
                      </td>

                      <td>
                        <div class="admin-table__actions">
                          <a 
                            href="admin.php?editar=<?= e($producto['id']); ?>" 
                            class="admin-table__button admin-table__button--edit"
                          >
                            Editar
                          </a>

                          <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= e($producto['id']); ?>">

                            <button 
                              type="submit" 
                              class="admin-table__button admin-table__button--delete"
                              onclick="return confirm('¿Seguro que quieres eliminar este producto?')"
                            >
                              Eliminar
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>

                  <?php if (count($productos) === 0): ?>
                    <tr>
                      <td colspan="5">No hay productos registrados.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>