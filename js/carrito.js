/**
 * LUNAWEAR - Módulo del Carrito de Compras (PHP Session + JS)
 * Comunica con api/carrito.php y sincroniza el estado tanto en el navbar como en carrito.php
 */

const Carrito = {
  datos: {
    items: [],
    total_items: 0,
    subtotal: 0.0,
    costo_envio: 0.0,
    califica_envio_gratis: false,
    falta_para_envio_gratis: 50.0,
    total: 0.0
  },

  async init() {
    this.vincularEventos();
    await this.cargarCarrito();
  },

  async cargarCarrito() {
    try {
      const res = await fetch('api/carrito.php?accion=obtener');
      if (res.ok) {
        const json = await res.json();
        if (json.status === 'success' && json.carrito) {
          this.datos = json.carrito;
          this.actualizarUI();
        }
      }
    } catch (e) {
      console.warn('api/carrito.php no disponible, operando en modo local:', e);
    }
  },

  async agregar(producto, cantidad = 1) {
    const productoId = typeof producto === 'object' ? producto.id : producto;
    try {
      const res = await fetch('api/carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          accion: 'agregar',
          producto_id: productoId,
          cantidad: cantidad
        })
      });

      const json = await res.json();
      if (json.status === 'success' && json.carrito) {
        this.datos = json.carrito;
        this.actualizarUI();
        this.mostrarToast(json.mensaje || '¡Prenda añadida al carrito!');
      } else {
        alert(json.mensaje || 'Error al añadir al carrito');
      }
    } catch (e) {
      console.error('Error al agregar producto:', e);
    }
  },

  async cambiarCantidad(productoId, delta) {
    try {
      const res = await fetch('api/carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          accion: 'actualizar',
          producto_id: productoId,
          delta: delta
        })
      });

      const json = await res.json();
      if (json.status === 'success' && json.carrito) {
        this.datos = json.carrito;
        this.actualizarUI();
      }
    } catch (e) {
      console.error('Error al actualizar cantidad:', e);
    }
  },

  async eliminar(productoId) {
    try {
      const res = await fetch('api/carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          accion: 'eliminar',
          producto_id: productoId
        })
      });

      const json = await res.json();
      if (json.status === 'success' && json.carrito) {
        this.datos = json.carrito;
        this.actualizarUI();
        this.mostrarToast(json.mensaje || 'Producto eliminado del carrito');
      }
    } catch (e) {
      console.error('Error al eliminar producto:', e);
    }
  },

  async vaciar() {
    if (!this.datos.items || this.datos.items.length === 0) return;
    if (!confirm('¿Deseas vaciar todos los artículos de tu carrito?')) return;

    try {
      const res = await fetch('api/carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ accion: 'vaciar' })
      });

      const json = await res.json();
      if (json.status === 'success' && json.carrito) {
        this.datos = json.carrito;
        this.actualizarUI();
        this.mostrarToast('Carrito vaciado');
      }
    } catch (e) {
      console.error('Error al vaciar carrito:', e);
    }
  },

  actualizarUI() {
    this.actualizarBadges();
    this.renderPaginaCarrito();
  },

  actualizarBadges() {
    const badges = document.querySelectorAll('.cart-count-badge');
    const totalCount = this.datos.total_items || 0;
    badges.forEach(b => {
      b.textContent = totalCount;
      b.style.display = totalCount > 0 ? 'inline-block' : 'none';
    });
  },

  renderPaginaCarrito() {
    const contenedorItems = document.getElementById('cartPageItemsContainer');
    const vistaVacia = document.getElementById('cartEmptyView');
    const vistaContenido = document.getElementById('cartContentView');

    if (!contenedorItems) return; // No estamos en carrito.php

    const items = this.datos.items || [];

    if (items.length === 0) {
      if (vistaVacia) vistaVacia.style.display = 'block';
      if (vistaContenido) vistaContenido.style.display = 'none';
      return;
    }

    if (vistaVacia) vistaVacia.style.display = 'none';
    if (vistaContenido) vistaContenido.style.display = 'flex';

    let html = `
      <div class="table-responsive">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Prenda</th>
              <th>Precio</th>
              <th class="text-center">Cantidad</th>
              <th class="text-end">Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
    `;

    items.forEach(item => {
      const subtotalItem = (item.precio * item.cantidad).toFixed(2);
      html += `
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <img src="${item.imagen_url}" alt="${item.nombre}" class="cart-item-img">
              <div>
                <div class="cart-item-cat">${item.categoria_nombre || 'Colección'}</div>
                <div class="cart-item-title">${item.nombre}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="fw-bold">€${Number(item.precio).toFixed(2)}</span>
          </td>
          <td class="text-center">
            <div class="d-inline-flex align-items-center gap-1">
              <button type="button" class="cart-qty-btn" onclick="Carrito.cambiarCantidad(${item.id}, -1)">-</button>
              <span class="cart-qty-val">${item.cantidad}</span>
              <button type="button" class="cart-qty-btn" onclick="Carrito.cambiarCantidad(${item.id}, 1)">+</button>
            </div>
          </td>
          <td class="text-end">
            <strong class="fs-6">€${subtotalItem}</strong>
          </td>
          <td class="text-end">
            <button type="button" class="cart-remove-btn" onclick="Carrito.eliminar(${item.id})" title="Eliminar ${item.nombre}">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </button>
          </td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    contenedorItems.innerHTML = html;

    // Actualizar resumen
    const elSubtotal = document.getElementById('cartSummarySubtotal');
    const elEnvio = document.getElementById('cartSummaryShipping');
    const elTotal = document.getElementById('cartSummaryTotal');

    if (elSubtotal) elSubtotal.textContent = `€${this.datos.subtotal.toFixed(2)}`;
    if (elEnvio) {
      if (this.datos.costo_envio === 0) {
        elEnvio.innerHTML = '<span class="text-success fw-bold">GRATIS</span>';
      } else {
        elEnvio.textContent = `€${this.datos.costo_envio.toFixed(2)}`;
      }
    }
    if (elTotal) elTotal.textContent = `€${this.datos.total.toFixed(2)}`;
  },

  mostrarToast(mensaje) {
    let container = document.getElementById('lunaToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'lunaToastContainer';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'luna-toast';
    toast.innerHTML = `
      <span class="luna-toast__icon">✓</span>
      <span>${mensaje}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('luna-toast--show'), 20);
    setTimeout(() => {
      toast.classList.remove('luna-toast--show');
      setTimeout(() => toast.remove(), 400);
    }, 2800);
  },

  abrirCheckout() {
    if (!this.datos.items || this.datos.items.length === 0) {
      alert('Tu carrito está vacío');
      return;
    }
    const modalEl = document.getElementById('checkoutModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    }
  },

  async procesarCompra(e) {
    if (e) e.preventDefault();

    const nombre = document.getElementById('checkoutNombre')?.value || '';
    const email = document.getElementById('checkoutEmail')?.value || '';
    const telefono = document.getElementById('checkoutTelefono')?.value || '';
    const direccion = document.getElementById('checkoutDireccion')?.value || '';
    const metodoPago = document.getElementById('checkoutMetodoPago')?.value || 'tarjeta';

    try {
      const res = await fetch('api/carrito.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          accion: 'checkout',
          nombre,
          email,
          telefono,
          direccion,
          metodo_pago: metodoPago
        })
      });

      const json = await res.json();
      if (json.status === 'success') {
        const modalBody = document.getElementById('checkoutModalBody');
        if (modalBody) {
          modalBody.innerHTML = `
            <div class="text-center py-4">
              <div style="font-size: 3.5rem; color: #b77b57;" class="mb-3">✓</div>
              <h3 class="fw-bold mb-2">¡Pedido Confirmado!</h3>
              <p class="text-muted mb-4">Gracias <strong>${json.orden.cliente}</strong>. Tu pedido <span class="badge bg-dark">${json.orden.codigo}</span> ha sido registrado con éxito.</p>
              <div class="alert alert-light border p-3 text-start mb-4">
                <div><strong>Total Pagado:</strong> €${Number(json.orden.total).toFixed(2)}</div>
                <div><strong>Envío:</strong> ${json.orden.envio === 0 ? 'Gratis' : '€' + Number(json.orden.envio).toFixed(2)}</div>
                <small class="text-muted">Hemos enviado la confirmación detallada a tu correo.</small>
              </div>
              <button type="button" class="button button--dark w-100" data-bs-dismiss="modal" onclick="location.href='index.php'">
                Volver a la Tienda
              </button>
            </div>
          `;
        }
        this.datos = json.carrito;
        this.actualizarUI();
      } else {
        alert(json.mensaje || 'Error al procesar el pedido');
      }
    } catch (err) {
      console.error('Error en checkout:', err);
    }
  },

  vincularEventos() {
    const btnVaciar = document.getElementById('btnVaciarCarrito');
    if (btnVaciar) btnVaciar.addEventListener('click', () => this.vaciar());

    const btnCheckout = document.getElementById('btnCheckout');
    if (btnCheckout) btnCheckout.addEventListener('click', () => this.abrirCheckout());

    const formCheckout = document.getElementById('formCheckoutModal');
    if (formCheckout) formCheckout.addEventListener('submit', (e) => this.procesarCompra(e));
  }
};

document.addEventListener('DOMContentLoaded', () => {
  Carrito.init();
});
