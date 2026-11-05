<?php
require __DIR__ . '/functions.php';
$tipo = val('tipo'); $d = val('desde'); $h = val('hasta'); $qs = val('q'); $w = ['1']; $p = [];
if ($tipo !== '') { $w[] = 'tipo=?'; $p[] = $tipo; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) { $w[] = 'fecha>=?'; $p[] = "$d 00:00:00"; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $h)) { $w[] = 'fecha<=?'; $p[] = "$h 23:59:59"; }
if ($qs !== '') { $w[] = 'descripcion LIKE ?'; $p[] = "%$qs%"; }
page_start('Historial de movimientos', 'historial');
echo '<div class="box"><form class="filters" method="get"><label>Tipo<select name="tipo"><option value="">Todos</option>';
foreach (q('SELECT DISTINCT tipo FROM movimientos ORDER BY tipo') as $t) echo '<option' . ($t['tipo'] === $tipo ? ' selected' : '') . '>' . e($t['tipo']) . '</option>';
echo '</select></label><label>Desde<input type="date" name="desde" value="' . e($d) . '"></label><label>Hasta<input type="date" name="hasta" value="' . e($h) . '"></label><label>Texto<input name="q" value="' . e($qs) . '"></label><button class="btn s">Filtrar</button></form><table><tr><th>Fecha</th><th>Tipo</th><th>Elemento</th><th>Descripción</th></tr>';
foreach (q('SELECT * FROM movimientos WHERE ' . implode(' AND ', $w) . ' ORDER BY fecha DESC, id DESC LIMIT 300', $p)->fetchAll() as $m)
    echo '<tr><td>' . e(date('d/m/Y H:i', strtotime($m['fecha']))) . '</td><td>' . e($m['tipo']) . '</td><td>' . e($m['entidad'] . ' #' . $m['entidad_id']) . '</td><td>' . e($m['descripcion']) . '</td></tr>';
echo '</table><small>Se muestran hasta 300 movimientos.</small></div>';
page_end();
