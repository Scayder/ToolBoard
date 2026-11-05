<?php
require __DIR__ . '/functions.php';
$tipos = ['inventario' => 'Inventario completo', 'disponibles' => 'Disponibles y prestadas', 'prestamos' => 'Préstamos activos', 'atrasados' => 'Préstamos atrasados', 'mantenimiento' => 'Herramientas en mantenimiento', 'historial' => 'Historial de movimientos'];
$t = isset($tipos[val('tipo')]) ? val('tipo') : 'inventario';
$cat = (int)val('cat'); $d = val('desde'); $h = val('hasta');
$fd = preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : ''; $fh = preg_match('/^\d{4}-\d{2}-\d{2}$/', $h) ? $h : '';
$cw = $cat ? ' AND categoria_id=' . $cat : '';
$pq = "SELECT p.id, h.nombre AS herramienta, CONCAT(pe.apellido,', ',pe.nombre) AS persona, p.cantidad, p.fecha_prestamo, p.fecha_prevista, IF(p.fecha_prevista<NOW(),'Atrasado','En plazo') AS situacion FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal pe ON pe.id=p.personal_id WHERE p.estado='activo'";
$params = [];
switch ($t) {
    case 'inventario': $sql = "SELECT codigo, nombre, categoria, marca, ubicacion, cantidad_total AS total, disponible, prestadas, en_mant AS mantenimiento, fuera AS fuera_servicio FROM v_herramientas WHERE activo=1$cw ORDER BY nombre"; break;
    case 'disponibles': $sql = "SELECT codigo, nombre, categoria, ubicacion, disponible, prestadas FROM v_herramientas WHERE activo=1 AND (disponible>0 OR prestadas>0)$cw ORDER BY nombre"; break;
    case 'prestamos': $sql = "$pq ORDER BY p.fecha_prevista"; break;
    case 'atrasados': $sql = "$pq AND p.fecha_prevista<NOW() ORDER BY p.fecha_prevista"; break;
    case 'mantenimiento': $sql = "SELECT m.id, h.nombre AS herramienta, m.cantidad, m.motivo, m.responsable, m.estado, m.fecha_registro FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id WHERE m.estado<>'finalizado' ORDER BY m.fecha_registro"; break;
    default:
        $w = ['1']; if ($fd) { $w[] = 'fecha>=?'; $params[] = "$fd 00:00:00"; } if ($fh) { $w[] = 'fecha<=?'; $params[] = "$fh 23:59:59"; }
        $sql = 'SELECT fecha, tipo, entidad, descripcion FROM movimientos WHERE ' . implode(' AND ', $w) . ' ORDER BY fecha DESC, id DESC LIMIT 1000';
}
$rows = q($sql, $params)->fetchAll();
if (val('csv') === '1') {
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="toolboard_' . $t . '.csv"');
    echo "\xEF\xBB\xBF"; $o = fopen('php://output', 'w');
    if ($rows) { fputcsv($o, array_keys($rows[0]), ';'); foreach ($rows as $r) fputcsv($o, array_map(fn($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'" . $v : $v, $r), ';'); }
    exit;
}
$qs = http_build_query(['tipo' => $t, 'cat' => $cat, 'desde' => $fd, 'hasta' => $fh, 'csv' => 1]);
page_start('Reportes', 'reportes', '<button class="btn s" onclick="window.print()">Imprimir</button><a class="btn" href="' . e(app_url('reportes.php?' . $qs)) . '">Exportar CSV</a>');
echo '<div class="box"><form class="filters no-print" method="get"><label>Reporte<select name="tipo">';
foreach ($tipos as $k => $l) echo '<option value="' . $k . '"' . ($k === $t ? ' selected' : '') . '>' . e($l) . '</option>';
echo '</select></label><label>Categoría<select name="cat"><option value="0">Todas</option>';
foreach (q('SELECT * FROM categorias ORDER BY nombre') as $c) echo '<option value="' . $c['id'] . '"' . ($c['id'] == $cat ? ' selected' : '') . '>' . e($c['nombre']) . '</option>';
echo '</select></label><label>Desde<input type="date" name="desde" value="' . e($fd) . '"></label><label>Hasta<input type="date" name="hasta" value="' . e($fh) . '"></label><button class="btn s">Generar</button></form>';
echo '<h2>' . e($tipos[$t]) . ' — ' . date('d/m/Y H:i') . ' (' . count($rows) . ' registros)</h2><table>';
if ($rows) { echo '<tr>'; foreach (array_keys($rows[0]) as $k) echo '<th>' . e(str_replace('_', ' ', $k)) . '</th>'; echo '</tr>'; foreach ($rows as $r) { echo '<tr>'; foreach ($r as $v) echo '<td>' . e($v) . '</td>'; echo '</tr>'; } }
else echo '<tr><td>Sin datos para este reporte.</td></tr>';
echo '</table></div>';
page_end();
