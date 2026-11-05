<?php
require __DIR__ . 'functions.php';
$t = q('SELECT COALESCE(SUM(cantidad_total),0) tot, COALESCE(SUM(disponible),0) disp, COALESCE(SUM(prestadas),0) pre, COALESCE(SUM(en_mant),0) man, COALESCE(SUM(fuera),0) fue FROM v_herramientas WHERE activo=1')->fetch();
$pend = (int)q("SELECT COUNT(*) FROM prestamos WHERE estado='activo'")->fetchColumn();
$atr = q("SELECT p.*, h.nombre hn, CONCAT(pe.nombre,' ',pe.apellido) persona FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal pe ON pe.id=p.personal_id WHERE p.estado='activo' AND p.fecha_prevista < NOW() ORDER BY p.fecha_prevista")->fetchAll();
$mant = q("SELECT m.*, h.nombre hn FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id WHERE m.estado='pendiente' ORDER BY m.fecha_registro")->fetchAll();
$movs = q('SELECT * FROM movimientos ORDER BY fecha DESC, id DESC LIMIT 8')->fetchAll();
page_start('Inicio', 'dashboard', '<a class="btn" href="' . e(app_url('prestamos.php?a=nuevo')) . '">+ Nuevo préstamo</a>');
$cards = [['Total de herramientas', $t['tot'], ''], ['Disponibles', $t['disp'], ''], ['Prestadas', $t['pre'], 'o'], ['En mantenimiento', $t['man'], 'o'], ['Fuera de servicio', $t['fue'], 'r'], ['Préstamos pendientes', $pend, ''], ['Atrasados', count($atr), 'r']];
echo '<div class="cards">';
foreach ($cards as $c) echo '<div class="card ' . $c[2] . '"><small>' . e($c[0]) . '</small><b data-count="' . (int)$c[1] . '">' . (int)$c[1] . '</b></div>';
echo '</div><div class="box"><h2>Alertas</h2>';
if (!$atr && !$mant) echo '<p>Sin alertas.</p>'; else {
    echo '<ul class="alerts">';
    foreach ($atr as $a) echo '<li><span class="badge bad">Atrasado</span> ' . e($a['cantidad'] . ' x ' . $a['hn']) . ' — ' . e($a['persona']) . ' (debía volver el ' . e(date('d/m/Y H:i', strtotime($a['fecha_prevista']))) . ')</li>';
    foreach ($mant as $m) echo '<li><span class="badge warn">Mantenimiento pendiente</span> ' . e($m['hn']) . ': ' . e($m['motivo']) . '</li>';
    echo '</ul>';
}
echo '</div><div class="box"><h2>Movimientos recientes</h2><table><tr><th>Fecha</th><th>Tipo</th><th>Descripción</th></tr>';
foreach ($movs as $m) echo '<tr><td>' . e(date('d/m/Y H:i', strtotime($m['fecha']))) . '</td><td>' . e($m['tipo']) . '</td><td>' . e($m['descripcion']) . '</td></tr>';
echo '</table></div>';
page_end();
