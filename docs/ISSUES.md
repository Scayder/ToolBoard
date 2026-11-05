# Los 10 Issues de ToolBoard
Formato de rama: `feature/issue-NN-nombre`, creada desde `main` actualizada. Un Pull Request por Issue con `Closes #N`.

## Issue 1 — Estructura inicial, configuración y base de datos · Integrante 1 · `feature/issue-01-estructura` · Sin dependencias
**Descripción:** base del proyecto sobre la que trabajarán todos. **Objetivo:** que el proyecto arranque y se conecte a la BD.
**Tareas:** crear carpetas, `.gitignore`, `db.php`, `functions.php` (PDO, `e()`, `q()`, `tx()`, CSRF, `mov()`) y `database/toolboard.sql`.
**Criterios:** importar el SQL sin errores; la conexión funciona; existen las 7 tablas y la vista. **Resultado:** esqueleto fusionado en `main` el día 1-2.

## Issue 2 — Interfaz principal, navegación y dashboard · Integrante 1 · `feature/issue-02-dashboard` · Depende de 1
**Tareas:** `style.css` (paleta, responsive, impresión), menú lateral, `inicio.php` con tarjetas, alertas y movimientos recientes.
**Criterios:** las cifras coinciden con la BD; se ve bien en 400 px y en escritorio; las alertas muestran atrasos y mantenimientos pendientes.

## Issue 3 — Inventario y categorías · Integrante 2 · `feature/issue-03-inventario` · Depende de 1
**Tareas:** alta, edición y baja de herramientas; CRUD de categorías; registro en historial.
**Criterios:** no se duplican códigos; no se elimina con préstamos activos; con historial se hace baja lógica.

## Issue 4 — Búsqueda, filtros y validaciones del inventario · Integrante 2 · `feature/issue-04-filtros` · Depende de 3
**Tareas:** búsqueda por nombre/código, filtros por categoría y estado, validaciones de servidor, no reducir el total por debajo de las unidades comprometidas.
**Criterios:** los filtros se combinan; datos inválidos muestran mensajes claros.

## Issue 5 — Personal del taller · Integrante 3 · `feature/issue-05-personal` · Depende de 1
**Tareas:** CRUD de personal, estado activo/inactivo, búsqueda.
**Criterios:** documento único; no se elimina a quien tiene historial (se inactiva).

## Issue 6 — Registro y control de préstamos · Integrante 3 · `feature/issue-06-prestamos` · Depende de 3 y 5
**Tareas:** formulario de préstamo, transacción con bloqueo, listado con filtros (activos/atrasados/devueltos).
**Criterios:** no se prestan más unidades que las disponibles, ni herramientas fuera de servicio, ni a personas inactivas; los atrasos se identifican.

## Issue 7 — Devoluciones y existencias · Integrante 4 · `feature/issue-07-devoluciones` · Depende de 6
**Tareas:** pantalla de devolución con estado (buena/dañada/mantenimiento), actualización de existencias, creación de incidencia.
**Criterios:** al devolver, la disponibilidad se actualiza; si vuelve dañada se crea un mantenimiento pendiente.

## Issue 8 — Mantenimiento, incidencias y herramientas fuera de servicio · Integrante 4 · `feature/issue-08-mantenimiento` · Depende de 3
**Tareas:** alta de incidencia, estados pendiente/en proceso/finalizado, resultado al finalizar (disponible o fuera de servicio).
**Criterios:** un mantenimiento finalizado no se modifica; lo en mantenimiento no se presta.

## Issue 9 — Historial de movimientos y reportes · Integrante 5 · `feature/issue-09-historial-reportes` · Depende de 1
**Tareas:** consulta del historial con filtros, 6 reportes, hoja de estilos de impresión, exportación CSV.
**Criterios:** el historial no se pierde al eliminar registros; los reportes coinciden con la BD; el CSV abre en Excel.

## Issue 10 — Integración final, pruebas y documentación · Integrante 5 · `feature/issue-10-integracion` · Depende de 1 a 9
**Tareas:** fusionar y resolver conflictos, ejecutar `tests/pruebas_manuales.md`, corregir errores, completar README (nombres y aportes).
**Criterios:** `main` funciona de punta a punta; la tabla de pruebas está completada con resultados reales; el README está completo.
