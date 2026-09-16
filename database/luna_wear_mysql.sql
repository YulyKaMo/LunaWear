-- =======================================================
-- LUNAWEAR - BASE DE DATOS PARA XAMPP (MySQL / MariaDB)
-- Archivo listo para importar desde phpMyAdmin
-- =======================================================

CREATE DATABASE IF NOT EXISTS `luna_wear` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `luna_wear`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `pedido_detalles`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `usuarios`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabla de Categorías
CREATE TABLE `categorias` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `activo` TINYINT(1) DEFAULT 1,
  `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabla de Productos
CREATE TABLE `productos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `imagen_url` VARCHAR(500) NOT NULL,
  `etiqueta` VARCHAR(50) DEFAULT 'Colección',
  `destacado` TINYINT(1) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  `categoria_id` INT NOT NULL,
  `rating_rate` DECIMAL(2,1) DEFAULT 4.5,
  `rating_count` INT DEFAULT 50,
  `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla de Usuarios (Administrador)
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `rol` VARCHAR(50) DEFAULT 'admin',
  `activo` TINYINT(1) DEFAULT 1,
  `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla de Pedidos (Compras realizadas)
CREATE TABLE `pedidos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo_orden` VARCHAR(50) NOT NULL UNIQUE,
  `cliente_nombre` VARCHAR(150) NOT NULL,
  `cliente_email` VARCHAR(150) NOT NULL,
  `cliente_telefono` VARCHAR(50) NOT NULL,
  `direccion_envio` TEXT NOT NULL,
  `metodo_pago` VARCHAR(50) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `costo_envio` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `estado` VARCHAR(50) DEFAULT 'confirmado',
  `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla de Detalles de Pedido
CREATE TABLE `pedido_detalles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pedido_id` INT NOT NULL,
  `producto_id` INT NOT NULL,
  `nombre_producto` VARCHAR(255) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `cantidad` INT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- DATOS INICIALES: Categorías
-- =======================================================
INSERT INTO `categorias` (`id`, `nombre`, `slug`) VALUES
(1, 'Mujer', 'mujer'),
(2, 'Hombre', 'hombre'),
(3, 'Chaquetas', 'chaquetas'),
(4, 'Accesorios', 'accesorios');

-- =======================================================
-- DATOS INICIALES: Usuario Administrador (clave: admin123)
-- =======================================================
INSERT INTO `usuarios` (`usuario`, `password`, `rol`, `activo`) VALUES
('admin', '$2y$10$.UVZWM7xsacOsajpgydzEOS5Ij1PQV29CLRjoOaEuG5IokHXhDHhS', 'admin', 1);

-- =======================================================
-- DATOS INICIALES: Productos por Categoría
-- =======================================================

-- CATEGORÍA 1: MUJER
INSERT INTO `productos` (`nombre`, `descripcion`, `precio`, `imagen_url`, `etiqueta`, `destacado`, `activo`, `categoria_id`, `rating_rate`, `rating_count`) VALUES
('Blazer Entallado Negro', 'Blazer estructurado con solapa clásica y botones carey para looks formales y de oficina.', 74.90, 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=700&q=80', 'Elegante', 1, 1, 1, 4.8, 98),
('Vestido Camisero Floral', 'Vestido midi de manga corta con estampado botánico sutil, abotonado frontal y cinturón de tela.', 54.90, 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&w=700&q=80', 'Tendencia', 1, 1, 1, 4.8, 120),
('Blusa Escote Barco Soft Touch', 'Prenda ligera con caída fluida y tejido modal elástico para máxima comodidad y estilo chic.', 19.90, 'https://fakestoreapi.com/img/71z3kpMAYsL._AC_UY879_t.png', 'Esencial', 1, 1, 1, 4.7, 130),
('Camiseta Deportiva Transpirable', 'Camiseta atlética de secado rápido con tecnología de ventilación activa para entrenar con estilo.', 16.90, 'https://fakestoreapi.com/img/51eg55uWmdL._AC_UX679_t.png', 'Sport', 0, 1, 1, 4.5, 146),
('Camiseta Casual Estampada Luna', 'Camiseta con cuello redondo holgado y diseño gráfico sutil en algodón 100% orgánico.', 18.50, 'https://fakestoreapi.com/img/61pHAEJ4NML._AC_UX679_t.png', 'Nuevo', 0, 1, 1, 4.6, 145);

-- CATEGORÍA 2: HOMBRE
INSERT INTO `productos` (`nombre`, `descripcion`, `precio`, `imagen_url`, `etiqueta`, `destacado`, `activo`, `categoria_id`, `rating_rate`, `rating_count`) VALUES
('Camiseta Premium Slim Fit', 'Camiseta masculina de algodón peinado con corte entallado y tacto ultrasuave para looks diarios.', 22.90, 'https://fakestoreapi.com/img/71-3HjGNDUL._AC_SY879._SX._UX._SY._UY_t.png', 'Básico', 1, 1, 2, 4.7, 259),
('Camisa Casual Manga Larga Slim Fit', 'Camisa elegante y versátil confeccionada en popelín de alta durabilidad con botones contrastados.', 34.90, 'https://fakestoreapi.com/img/71YXzeOuslL._AC_UY879_t.png', 'Elegante', 1, 1, 2, 4.5, 430),
('Jeans Slim Urbano Azul', 'Pantalón denim con ligero lavado desteñido y tiro medio confeccionado en algodón elástico.', 49.90, 'https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=700&q=80', 'Nuevo', 1, 1, 2, 4.7, 85),
('Polo Piqué Algodón Marino', 'Polo clásico confeccionado en piqué de algodón peinado con cuello y ribetes en contraste.', 29.90, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?auto=format&fit=crop&w=700&q=80', 'Clásico', 0, 1, 2, 4.5, 47);

-- CATEGORÍA 3: CHAQUETAS
INSERT INTO `productos` (`nombre`, `descripcion`, `precio`, `imagen_url`, `etiqueta`, `destacado`, `activo`, `categoria_id`, `rating_rate`, `rating_count`) VALUES
('Abrigo Camel Elegance', 'Abrigo de paño de corte recto con solapa amplia y cinturón a juego. Sofisticación pura.', 89.90, 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?auto=format&fit=crop&w=700&q=80', 'Premium', 1, 1, 3, 4.9, 142),
('Chaqueta Casual de Algodón', 'Chaqueta ligera con múltiples bolsillos y cuello camisero. Perfecta para entretiempo y noches frescas.', 55.90, 'https://fakestoreapi.com/img/71li-ujtlUL._AC_UX679_t.png', 'Tendencia', 1, 1, 3, 4.7, 500),
('Cazadora Biker de Cuero Sintético', 'Chaqueta estilo motero con capucha extraíble y cremalleras metálicas. Imprescindible en tu armario.', 49.95, 'https://fakestoreapi.com/img/81XH0e8fefL._AC_UY879_t.png', 'Bestseller', 1, 1, 3, 4.6, 340),
('Chaqueta Técnica 3 en 1 Térmica', 'Abrigo impermeable para invierno con forro polar desmontable y capucha ajustable anti-viento.', 69.90, 'https://fakestoreapi.com/img/51Y5NI-I5jL._AC_UX679_t.png', 'Invierno', 1, 1, 3, 4.8, 235),
('Cortavientos Marinero Impermeable', 'Chaqueta ligera impermeable con forro a rayas, cordón de ajuste en cintura y bolsillos frontales.', 39.90, 'https://fakestoreapi.com/img/71HblAHs5xL._AC_UY879_-2t.png', 'Práctico', 0, 1, 3, 4.5, 679),
('Gabardina Trench Clásica Beige', 'Gabardina impermeable de doble botonadura con trabillas en hombros y cinturón ajustable.', 99.90, 'https://images.unsplash.com/photo-1520975954732-35dd22299614?auto=format&fit=crop&w=700&q=80', 'Edición Especial', 1, 1, 3, 4.9, 134);

-- CATEGORÍA 4: ACCESORIOS
INSERT INTO `productos` (`nombre`, `descripcion`, `precio`, `imagen_url`, `etiqueta`, `destacado`, `activo`, `categoria_id`, `rating_rate`, `rating_count`) VALUES
('Mochila Urbana Foldsack No. 1', 'Mochila clásica y funcional con compartimento acolchado para laptop de 15''. Ideal para el día a día.', 64.90, 'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_t.png', 'Bestseller', 1, 1, 4, 4.6, 120),
('Bolso Tote de Piel Minimalista', 'Bolso espacioso de cuero sintético granulado con asas reforzadas y cierre magnético.', 39.90, 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=700&q=80', 'Accesorio', 1, 1, 4, 4.9, 110),
('Sneakers Blancas Retro Street', 'Zapatillas deportivas urbanas con plantilla acolchada ergonómica y suela de caucho vulcanizado.', 59.90, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=700&q=80', 'Tendencia', 1, 1, 4, 4.6, 64),
('Brazalete Legend Dragón en Oro y Plata', 'Brazalete artesanal trenzado a mano con detalles en oro y plata de ley. Una pieza statement única.', 149.00, 'https://fakestoreapi.com/img/71pWzhdJNwL._AC_UL640_QL65_ML3_t.png', 'Premium', 1, 1, 4, 4.8, 400),
('Anillo Micropavé en Oro Macizo', 'Anillo minimalista elaborado en oro de 14k engastado con zirconitas pulidas de brillo excepcional.', 89.00, 'https://fakestoreapi.com/img/61sbMiUnoGL._AC_UL640_QL65_ML3_t.png', 'Exclusivo', 0, 1, 4, 4.7, 70),
('Anillo Corona Princesa Oro Blanco', 'Elegante anillo chapado en oro blanco con cristal central corte princesa de alta refracción.', 24.90, 'https://fakestoreapi.com/img/71YAIFU48IL._AC_UL640_QL65_ML3_t.png', 'Nuevo', 0, 1, 4, 4.5, 400),
('Pendientes Dobles Oro Rosa', 'Juego de pendientes hipoalergénicos en acero inoxidable con baño de oro rosa pulido.', 18.90, 'https://fakestoreapi.com/img/51UDEzMJVpL._AC_UL640_QL65_ML3_t.png', 'Tendencia', 0, 1, 4, 4.6, 100);
