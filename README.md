# ToolBoard — Sistema de gestión y control de herramientas de un taller industrial

Aplicación web (dashboard) para administrar el inventario de herramientas de un taller, registrar préstamos y devoluciones, controlar el mantenimiento y consultar el historial y los reportes.

## Problema y objetivos
**Problema:** en un taller es difícil saber qué herramientas hay, dónde están y quién las tiene; las pérdidas y los daños pasan inadvertidos.
**Objetivo general:** ofrecer un sistema local, simple y confiable para controlar las herramientas del taller.
**Objetivos específicos:** (1) administrar el inventario; (2) registrar préstamos y devoluciones sin inconsistencias; (3) registrar mantenimientos e incidencias; (4) conservar un historial de movimientos; (5) emitir reportes imprimibles y exportables a CSV.

## Tecnologías
HTML5, CSS3, JavaScript, PHP 8 (PDO), MySQL/MariaDB, phpMyAdmin, XAMPP, VS Code, Git y GitHub. Sin frameworks ni servicios pagos.

## Instalación en XAMPP
1. Instalar XAMPP (PHP 8+) e iniciar **Apache** y **MySQL**.
2. Copiar la carpeta `toolboard` dentro de `C:\xampp\htdocs\`.
3. Abrir `http://localhost/phpmyadmin`, ir a **Importar** y elegir `database/toolboard.sql` (crea la base `toolboard`, las tablas y los datos de prueba).
4. Revisar `db.php` (por defecto: usuario `root`, sin contraseña). La raíz URL de la aplicación se determina automáticamente a partir del archivo PHP solicitado y su ubicación en el proyecto.
5. Abrir `http://localhost/toolboard/` (pantalla de presentación) y pulsar **Acceder al sistema**.

## Funcionalidades
Inicio con datos reales · Inventario (CRUD, categorías, búsqueda, filtros, baja lógica) · Préstamos con control de disponibilidad · Devoluciones (buen estado / dañada / mantenimiento) · Personal (CRUD; los inactivos no reciben préstamos) · Mantenimiento (pendiente, en proceso, finalizado) · Historial · Reportes (impresión y CSV).

**Decisiones de diseño:** las cantidades disponibles no se guardan: la vista `v_herramientas` las calcula (total − prestadas − en mantenimiento − fuera de servicio). Préstamos, devoluciones y mantenimientos usan transacciones con bloqueo de fila. Las consultas son preparadas (PDO), toda salida se escapa con `htmlspecialchars` y los formularios llevan token CSRF.
**Fuera de alcance:** autenticación/login (cualquiera con acceso a la aplicación puede operar), paginación del inventario y préstamos de varias herramientas en una sola operación.

## Estructura
```
toolboard/
├── index.php              (pantalla de presentación)
├── inicio.php             (Inicio: indicadores y alertas)
├── inventario.php
├── personal.php
├── prestamos.php
├── mantenimiento.php
├── historial.php
├── reportes.php
├── db.php                 (conexión y configuración de URL)
├── functions.php          (seguridad, funciones comunes y layout)
├── style.css
├── landing.css
├── app.js
├── database/toolboard.sql
├── tests/pruebas_manuales.md
└── docs/{ISSUES.md,GUIA_GIT.md}
```

## Equipo (completar)
| Integrante | Nombre | Issues |
|---|---|---|
| 1 | ______ | 1, 2 |
| 2 | ______ | 3, 4 |
| 3 | ______ | 5, 6 |
| 4 | ______ | 7, 8 |
| 5 | ______ | 9, 10 |

Detalle de los diez Issues: `docs/ISSUES.md`. Estrategia de ramas y pasos de integración: `docs/GUIA_GIT.md`.

## Aporte de cada integrante (completar al finalizar)
Integrante 1: ______ · Integrante 2: ______ · Integrante 3: ______ · Integrante 4: ______ · Integrante 5: ______
(Indicar qué archivos programó y en qué Pull Request se integró.)

## Pruebas
Seguir `tests/pruebas_manuales.md` y completar la columna *Estado*. Reimportar el SQL para volver a los datos iniciales.
