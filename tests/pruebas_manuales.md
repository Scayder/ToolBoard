# Pruebas manuales de ToolBoard

Estado: **Pendiente** = aún no ejecutada. Cada integrante debe completar la columna al probar (Aprobada / Falló + fecha).
Antes de empezar, reimporte `database/toolboard.sql` para partir de datos limpios.

| # | Prueba | Pasos | Resultado esperado | Estado |
|---|---|---|---|---|
| 1 | Alta de herramienta | Inventario > Nueva herramienta, completar y guardar | Aparece en la tabla y en Historial (alta_herramienta) | Pendiente |
| 2 | Búsqueda y filtros | Buscar "taladro"; filtrar por categoría y por estado | La tabla muestra solo coincidencias | Pendiente |
| 3 | Código duplicado | Crear herramienta con código DES-001 | Mensaje de error, no se guarda | Pendiente |
| 4 | Alta de persona | Personal > Nueva persona | Aparece en la lista | Pendiente |
| 5 | Préstamo excesivo | Prestar más unidades que las disponibles | Error con unidades disponibles; no se crea | Pendiente |
| 6 | Herramienta fuera de servicio | Intentar prestar Martillo de bola (MAR-001, fuera de servicio) | No figura en el selector | Pendiente |
| 7 | Persona inactiva | Intentar prestar a Ines Cabrera | No figura en el selector | Pendiente |
| 8 | Préstamo válido | Prestar 1 Nivel láser | Disponibles bajan en 1; movimiento "prestamo" | Pendiente |
| 9 | Devolución buena | Devolver ese préstamo en buen estado | Disponibles vuelven a subir | Pendiente |
| 10 | Devolución dañada | Devolver un préstamo como "Dañada" | Se crea mantenimiento pendiente; baja la disponibilidad | Pendiente |
| 11 | Atrasados | Ver Préstamos > Atrasados y Inicio | La amoladora (préstamo de datos de prueba) figura atrasada | Pendiente |
| 12 | Mantenimiento | Gestionar: pendiente > en proceso > finalizado (disponible) | Estados se actualizan; la herramienta vuelve a estar disponible | Pendiente |
| 13 | Finalizar fuera de servicio | Finalizar otro mantenimiento como "fuera de servicio" | La unidad no vuelve a estar disponible | Pendiente |
| 14 | Eliminación protegida | Eliminar Taladro (con préstamo activo) | Mensaje de error; no se elimina | Pendiente |
| 15 | Historial | Revisar Historial tras las pruebas anteriores | Todos los movimientos figuran | Pendiente |
| 16 | Reportes | Generar cada reporte, imprimir, exportar CSV | Datos coherentes con las pantallas | Pendiente |
| 17 | Responsive | Reducir la ventana a ~400 px | Menú lateral colapsa en botón ☰; tablas con scroll | Pendiente |
| 18 | Rama main | Clonar main, instalar y repetir pruebas 1-16 | Todo funciona | Pendiente |
| 19 | Pantalla de presentación | Abrir /toolboard/ y pulsar «Acceder al sistema» | Se abre Inicio sin errores; «← Presentación» en el menú vuelve a la portada | Pendiente |
