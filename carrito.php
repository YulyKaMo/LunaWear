<?php
$activePage = 'carrito';
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Carrito de Compras | Luna Wear</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="cart-page">
    <div class="container">
      <div class="cart-page__header text-center">
        <h1 class="cart-page__title">Tu Carrito de Compras</h1>
        <p class="cart-page__subtitle">Revisa tus prendas seleccionadas antes de proceder al pago seguro.</p>
      </div>

      <!-- Vista cuando el carrito está vacío -->
      <div id="cartEmptyView" class="cart-card text-center py-5" style="display: none;">
        <div style="font-size: 3rem; margin-bottom: 16px;">🛍️</div>
        <h3 class="fw-bold mb-2">Tu carrito está vacío</h3>
        <p class="text-muted mb-4">Añade prendas de nuestras colecciones de Mujer, Hombre, Chaquetas o Accesorios.</p>
        <a href="index.php#productos" class="button button--dark">
          Descubrir Catálogo
        </a>
      </div>

      <!-- Vista con artículos en el carrito -->
      <div id="cartContentView" class="row g-4" style="display: none;">
        <!-- Lista de productos -->
        <div class="col-12 col-lg-8">
          <div class="cart-card">
            <div id="cartPageItemsContainer">
              <!-- Renderizado dinámicamente por js/carrito.js -->
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
              <a href="index.php#productos" class="button button--light">
                ← Seguir Comprando
              </a>
              <button type="button" class="btn btn-link text-danger text-decoration-none fw-bold" id="btnVaciarCarrito">
                Vaciar Carrito
              </button>
            </div>
          </div>
        </div>

        <!-- Resumen del pedido -->
        <div class="col-12 col-lg-4">
          <div class="cart-card" id="cartSummaryContainer">
            <h3 class="fw-bold mb-3" style="font-size: 1.4rem;">Resumen del Pedido</h3>
            
            <div class="cart-summary__row">
              <span>Subtotal</span>
              <strong id="cartSummarySubtotal">€0.00</strong>
            </div>

            <div class="cart-summary__row">
              <span>Gastos de envío</span>
              <strong id="cartSummaryShipping">€0.00</strong>
            </div>

            <p class="small text-muted mb-3">
              Envío <strong>GRATIS</strong> en compras a partir de €50.
            </p>

            <div class="cart-summary__row cart-summary__row--total">
              <span>Total</span>
              <strong id="cartSummaryTotal">€0.00</strong>
            </div>

            <button type="button" class="button button--dark w-100 text-center mt-3" id="btnCheckout">
              Tramitar Pedido
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal de Checkout / Tramitar Pedido -->
  <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow" style="border-radius: 24px;">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="checkoutModalLabel">Completar Pedido</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-4" id="checkoutModalBody">
          <form id="formCheckoutModal">
            <div class="mb-3">
              <label class="form-label fw-bold small">Nombre y Apellidos</label>
              <input type="text" id="checkoutNombre" class="form-control" placeholder="Ej: María García" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small">Correo Electrónico</label>
              <input type="email" id="checkoutEmail" class="form-control" placeholder="maria@ejemplo.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small">Teléfono</label>
              <input type="tel" id="checkoutTelefono" class="form-control" placeholder="+34 600 000 000" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small">Dirección de Entrega</label>
              <input type="text" id="checkoutDireccion" class="form-control" placeholder="Calle, número, ciudad, CP" required>
            </div>
            <div class="mb-4">
              <label class="form-label fw-bold small">Método de Pago</label>
              <select id="checkoutMetodoPago" class="form-select">
                <option value="tarjeta">Tarjeta de Crédito / Débito</option>
                <option value="paypal">PayPal</option>
                <option value="bizum">Bizum</option>
              </select>
            </div>
            <button type="submit" class="button button--dark w-100 text-center">
              Confirmar y Pagar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/carrito.js"></script>
</body>

</html>
