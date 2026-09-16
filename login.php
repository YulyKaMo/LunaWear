<?php
session_start();

require_once __DIR__ . '/../includes/csrf.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Login Admin | Luna Wear</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>

  <main class="login-page">
    <section class="login">
      <div class="login__card">
        <a href="index.php" class="login__logo">
          Luna<span class="login__logo-accent">Wear</span>
        </a>

        <h1 class="login__title">Iniciar sesión</h1>

        <p class="login__description">
          Ingresa con tu usuario administrador para gestionar los productos de la tienda.
        </p>

        <?php if (isset($_GET['error'])): ?>
          <p class="login-form__error">
            Usuario o contraseña incorrectos.
          </p>
        <?php endif; ?>

        <form class="login-form" action="procesar-login.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">

          <div class="login-form__group">
            <label for="usuario" class="login-form__label">Usuario</label>
            <input 
              type="text" 
              id="usuario" 
              name="usuario"
              class="login-form__input" 
              placeholder="admin"
              required
            />
          </div>

          <div class="login-form__group">
            <label for="password" class="login-form__label">Contraseña</label>
            <input 
              type="password" 
              id="password" 
              name="password"
              class="login-form__input" 
              placeholder="Tu contraseña"
              required
            />
          </div>

          <button type="submit" class="login-form__button">
            Entrar al panel
          </button>
        </form>

        <a href="index.php" class="login__back">
          Volver a la tienda
        </a>
      </div>
    </section>
  </main>

</body>
</html>