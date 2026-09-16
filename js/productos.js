/**
 * LUNAWEAR - Catálogo Dinámico desde Base de Datos
 * Carga productos de la base de datos a través de api/productos.php
 * Respeta 100% el diseño, clases y animaciones originales de LunaWear
 */

const Tienda = {
  productos: [],
  categoriaActiva: 'todos',

  async init() {
    this.vincularFiltros();
    await this.cargarProductos();
  },

  async cargarProductos() {
    try {
      const res = await fetch('api/productos.php');
      if (res.ok) {
        const data = await res.json();
        this.productos = data.productos || [];
      } else {
        throw new Error('API productos error');
      }
    } catch (e) {
      console.warn('Fallback a API de catálogo:', e);
      try {
        const res = await fetch('https://fakestoreapi.com/products');
        const data = await res.json();
        this.productos = data
          .filter(p => !p.category.includes('electronics'))
          .map(p => {
            let cat = 'mujer';
            const catLower = p.category.toLowerCase();
            const titleLower = p.title.toLowerCase();
            if (titleLower.includes('jacket') || titleLower.includes('coat')) cat = 'chaquetas';
            else if (catLower.includes('men')) cat = 'hombre';
            else if (catLower.includes('jewelery')) cat = 'accesorios';

            return {
              id: p.id,
              nombre: p.title,
              descripcion: p.description,
              precio: p.price,
              imagen_url: p.image,
              categoria_slug: cat,
              categoria_nombre: cat.charAt(0).toUpperCase() + cat.slice(1),
              etiqueta: 'Colección'
            };
          });
      } catch (err) {
        console.error('Error cargando catálogo:', err);
      }
    }

    this.renderizarCatalogo();
  },

  renderizarCatalogo() {
    const contenedor = document.getElementById('contenedorProductos');
    if (!contenedor) return;

    let filtrados = this.productos;
    if (this.categoriaActiva !== 'todos') {
      filtrados = this.productos.filter(p => 
        (p.categoria_slug || '').toLowerCase() === this.categoriaActiva.toLowerCase() ||
        (p.categoria || '').toLowerCase() === this.categoriaActiva.toLowerCase()
      );
    }

    if (filtrados.length === 0) {
      contenedor.innerHTML = `
        <div class="col-12 text-center py-5">
          <h3 class="section-heading__title">No hay productos en esta categoría</h3>
          <p class="section-heading__description">Pronto añadiremos nuevas prendas a esta sección.</p>
        </div>
      `;
      return;
    }

    let html = '';
    filtrados.forEach(prod => {
      const precio = Number(prod.precio).toFixed(2);
      const badge = prod.etiqueta || prod.categoria_nombre || 'Colección';

      html += `
        <div class="col-12 col-md-6 col-lg-4 mb-4">
          <article class="card product-card">
            <div class="card-img-top product-card__image" style="background-image: url('${prod.imagen_url}');" tabindex="0">
              <span class="badge product-card__badge"> ${badge} </span>
            </div>

            <div class="card-body product-card__content">
              <div class="product-card__details">
                <h3 class="card-title product-card__title">
                  ${prod.nombre}
                </h3>

                <p class="card-text product-card__description">
                  ${prod.descripcion}
                </p>
              </div>

              <div class="product-card__footer">
                <div class="product-card__price-box">
                  <span class="product-card__price"> €${precio} </span>
                </div>

                <button type="button" class="btn product-card__button" onclick="Tienda.agregarAlCarrito(${prod.id})">
                  Comprar
                </button>
              </div>
            </div>
          </article>
        </div>
      `;
    });

    contenedor.innerHTML = html;
  },

  vincularFiltros() {
    const items = document.querySelectorAll('.categories__item');
    items.forEach(item => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        items.forEach(btn => btn.classList.remove('categories__item--active'));
        item.classList.add('categories__item--active');

        this.categoriaActiva = item.getAttribute('data-categoria') || 'todos';
        this.renderizarCatalogo();
      });
    });
  },

  agregarAlCarrito(id) {
    const prod = this.productos.find(p => p.id === id);
    if (prod && typeof Carrito !== 'undefined') {
      Carrito.agregar(prod, 1);
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  Tienda.init();
});