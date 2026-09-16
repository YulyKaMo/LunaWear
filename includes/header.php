<?php
// Header modular para LunaWear
$activePage = $activePage ?? '';
?>
<header class="sticky-top">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm" data-bs-theme="dark">
    <div class="container">
      <a href="index.php" class="navbar-brand header__logo">
        Luna<span class="header__logo-accent">Wear</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLunaWear"
        aria-controls="navbarLunaWear" aria-expanded="false" aria-label="Abrir menú de navegación">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarLunaWear">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item">
            <a href="index.php" class="nav-link header__link <?= ($activePage === 'inicio') ? 'header__link--active' : ''; ?>" aria-current="page">
              Inicio
            </a>
          </li>

          <li class="nav-item">
            <a href="index.php#productos" class="nav-link header__link">
              Productos
            </a>
          </li>

          <li class="nav-item">
            <a href="contacto.php" class="nav-link header__link <?= ($activePage === 'contacto') ? 'header__link--active' : ''; ?>">
              Contacto
            </a>
          </li>

          <li class="nav-item ms-lg-3">
            <a href="carrito.php" class="nav-link header__link position-relative d-inline-flex align-items-center <?= ($activePage === 'carrito') ? 'header__link--active' : ''; ?>" aria-label="Carrito de compras" title="Ver Carrito">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
              <span class="cart-count-badge badge rounded-pill bg-danger" id="cartNavCount" style="display: none;">0</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
