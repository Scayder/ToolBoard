CREATE DATABASE IF NOT EXISTS toolboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE toolboard;
SET FOREIGN_KEY_CHECKS=0;
DROP VIEW IF EXISTS v_herramientas;
DROP TABLE IF EXISTS movimientos, mantenimientos, prestamos, personal, herramientas, categorias;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE herramientas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(30) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  categoria_id INT NOT NULL,
  descripcion VARCHAR(255) NOT NULL DEFAULT '',
  marca VARCHAR(60) NOT NULL DEFAULT '',
  cantidad_total INT NOT NULL CHECK (cantidad_total >= 0),
  ubicacion VARCHAR(80) NOT NULL DEFAULT '',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (nombre),
  CONSTRAINT fk_h_cat FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

CREATE TABLE personal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,
  apellido VARCHAR(60) NOT NULL,
  documento VARCHAR(20) NOT NULL UNIQUE,
  sector VARCHAR(60) NOT NULL,
  cargo VARCHAR(60) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE prestamos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  herramienta_id INT NOT NULL,
  personal_id INT NOT NULL,
  cantidad INT NOT NULL CHECK (cantidad > 0),
  fecha_prestamo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_prevista DATETIME NOT NULL,
  fecha_devolucion DATETIME NULL,
  estado ENUM('activo','devuelto') NOT NULL DEFAULT 'activo',
  observaciones VARCHAR(255) NOT NULL DEFAULT '',
  INDEX (estado, fecha_prevista),
  CONSTRAINT fk_p_h FOREIGN KEY (herramienta_id) REFERENCES herramientas(id),
  CONSTRAINT fk_p_per FOREIGN KEY (personal_id) REFERENCES personal(id)
) ENGINE=InnoDB;

CREATE TABLE mantenimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  herramienta_id INT NOT NULL,
  cantidad INT NOT NULL CHECK (cantidad > 0),
  motivo VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NOT NULL DEFAULT '',
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responsable VARCHAR(100) NOT NULL,
  estado ENUM('pendiente','en_proceso','finalizado') NOT NULL DEFAULT 'pendiente',
  resultado ENUM('disponible','fuera_servicio') NULL,
  observaciones VARCHAR(255) NOT NULL DEFAULT '',
  fecha_fin DATETIME NULL,
  INDEX (estado),
  CONSTRAINT fk_m_h FOREIGN KEY (herramienta_id) REFERENCES herramientas(id)
) ENGINE=InnoDB;

-- Sin claves foráneas a propósito: el historial se conserva aunque se borre el registro original.
CREATE TABLE movimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo VARCHAR(40) NOT NULL,
  entidad VARCHAR(30) NOT NULL,
  entidad_id INT NULL,
  descripcion VARCHAR(255) NOT NULL,
  INDEX (fecha), INDEX (tipo)
) ENGINE=InnoDB;

-- Las cantidades disponibles NO se guardan: se calculan aquí, siempre consistentes.
CREATE VIEW v_herramientas AS
SELECT t.*, (t.cantidad_total - t.prestadas - t.en_mant - t.fuera) AS disponible FROM (
  SELECT h.id, h.codigo, h.nombre, h.categoria_id, c.nombre AS categoria, h.descripcion, h.marca,
         h.cantidad_total, h.ubicacion, h.activo, h.fecha_registro,
    CAST(COALESCE((SELECT SUM(p.cantidad) FROM prestamos p WHERE p.herramienta_id=h.id AND p.estado='activo'),0) AS SIGNED) AS prestadas,
    CAST(COALESCE((SELECT SUM(m.cantidad) FROM mantenimientos m WHERE m.herramienta_id=h.id AND m.estado<>'finalizado'),0) AS SIGNED) AS en_mant,
    CAST(COALESCE((SELECT SUM(m.cantidad) FROM mantenimientos m WHERE m.herramienta_id=h.id AND m.estado='finalizado' AND m.resultado='fuera_servicio'),0) AS SIGNED) AS fuera
  FROM herramientas h JOIN categorias c ON c.id=h.categoria_id
) t;

INSERT INTO categorias(nombre) VALUES ('Herramientas manuales'),('Herramientas eléctricas'),('Equipos de medición'),('Accesorios');
INSERT INTO herramientas(codigo,nombre,categoria_id,descripcion,marca,cantidad_total,ubicacion) VALUES
('DES-001','Destornillador Phillips PH2',1,'Punta imantada','Stanley',12,'Panel A - Estante 1'),
('LLA-001','Juego de llaves combinadas',1,'Set 8-24 mm','Bahco',6,'Panel A - Estante 2'),
('PIN-001','Pinza universal',1,'Aislada 1000 V','Knipex',8,'Panel A - Estante 1'),
('TAL-001','Taladro percutor 13 mm',2,'750 W','Bosch',4,'Armario B'),
('AMO-001','Amoladora angular 115 mm',2,'850 W','DeWalt',3,'Armario B'),
('MUL-001','Multímetro digital',3,'True RMS','Fluke',5,'Vitrina C'),
('CAL-001','Calibre digital',3,'0-150 mm','Mitutoyo',3,'Vitrina C'),
('SIE-001','Sierra caladora',2,'600 W','Makita',2,'Armario B'),
('MAR-001','Martillo de bola',1,'500 g','Tramontina',6,'Panel A - Estante 3'),
('NIV-001','Nivel láser',3,'Autonivelante','Bosch',2,'Vitrina C');
INSERT INTO personal(nombre,apellido,documento,sector,cargo,activo) VALUES
('Lucas','Ferreyra','TB-001','Mecánica','Operario',1),
('Marta','Quiroga','TB-002','Electricidad','Técnica',1),
('Diego','Salinas','TB-003','Soldadura','Oficial',1),
('Paula','Mendez','TB-004','Mantenimiento','Jefa de sector',1),
('Raúl','Benítez','TB-005','Mecánica','Aprendiz',1),
('Ines','Cabrera','TB-006','Electricidad','Técnica',0);
INSERT INTO prestamos(herramienta_id,personal_id,cantidad,fecha_prestamo,fecha_prevista,fecha_devolucion,estado,observaciones) VALUES
(4,1,1,NOW()-INTERVAL 1 DAY,NOW()+INTERVAL 2 DAY,NULL,'activo','Trabajo en banco 2'),
(6,2,2,NOW()-INTERVAL 1 DAY,NOW()+INTERVAL 1 DAY,NULL,'activo','Revisión de tableros'),
(5,3,1,NOW()-INTERVAL 6 DAY,NOW()-INTERVAL 3 DAY,NULL,'activo','Corte de perfiles'),
(1,4,3,NOW()-INTERVAL 5 DAY,NOW()-INTERVAL 4 DAY,NOW()-INTERVAL 4 DAY,'devuelto','Sin novedades'),
(3,1,2,NOW()-INTERVAL 4 DAY,NOW()-INTERVAL 2 DAY,NOW()-INTERVAL 2 DAY,'devuelto','');
INSERT INTO mantenimientos(herramienta_id,cantidad,motivo,descripcion,fecha_registro,responsable,estado,resultado,observaciones,fecha_fin) VALUES
(8,1,'Daño','Hoja de sierra rota',NOW()-INTERVAL 2 DAY,'Paula Mendez','pendiente',NULL,'',NULL),
(7,1,'Calibración','Desvío de lectura',NOW()-INTERVAL 3 DAY,'Paula Mendez','en_proceso',NULL,'Enviado a calibrar',NULL),
(9,1,'Mango roto','Irreparable',NOW()-INTERVAL 7 DAY,'Paula Mendez','finalizado','fuera_servicio','Se da de baja',NOW()-INTERVAL 6 DAY);
INSERT INTO movimientos(fecha,tipo,entidad,entidad_id,descripcion) VALUES
(NOW()-INTERVAL 8 DAY,'alta_herramienta','herramienta',1,'Alta inicial del inventario'),
(NOW()-INTERVAL 5 DAY,'prestamo','prestamo',4,'Préstamo de 3 x Destornillador Phillips PH2 a Paula Mendez'),
(NOW()-INTERVAL 4 DAY,'devolucion','prestamo',4,'Devolución en buen estado (3 unidades)'),
(NOW()-INTERVAL 6 DAY,'prestamo','prestamo',3,'Préstamo de 1 x Amoladora angular 115 mm a Diego Salinas'),
(NOW()-INTERVAL 3 DAY,'inicio_mantenimiento','mantenimiento',2,'Calibre digital en calibración'),
(NOW()-INTERVAL 2 DAY,'incidencia','mantenimiento',1,'Sierra caladora: hoja de sierra rota'),
(NOW()-INTERVAL 1 DAY,'prestamo','prestamo',1,'Préstamo de 1 x Taladro percutor 13 mm a Lucas Ferreyra'),
(NOW()-INTERVAL 1 DAY,'prestamo','prestamo',2,'Préstamo de 2 x Multímetro digital a Marta Quiroga');
