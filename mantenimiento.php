<?php
require __DIR__ . '/functions.php';
$self = '/mantenimiento.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = val('op');
    if ($op === 'nuevo') {
        $h = (int)val('herramienta_id'); $c = val('cantidad'); $mo = val('motivo'); $de = val('descripcion'); $re = val('responsable'); $err = [];
        if (!ctype_digit($c) || (int)$c < 1) $err[] = 'La cantidad debe ser un entero de al menos 1.';
        if ($mo === '' || strlen($mo) > 100) $err[] = 'El motivo es obligatorio (máx. 100 caracteres).';
        if ($re === '' || strlen($re) > 100) $err[] = 'El responsable es obligatorio.';
        if (strlen($de) > 255) $err[] = 'Descripción demasiado larga.';
        if (!$err) {
            $m = tx(function () use ($h, $c, $mo, $de, $re) {
                if (!q('SELECT id FROM herramientas WHERE id=? AND activo=1 FOR UPDATE', [$h])->fetch()) throw new Exception('Herramienta inválida.');
                $v = q('SELECT nombre, disponible FROM v_herramientas WHERE id=?', [$h])->fetch();
                if ((int)$c > $v['disponible']) throw new Exception('Solo hay ' . $v['disponible'] . ' unidad(es) disponible(s) para enviar a mantenimiento.');
                q('INSERT INTO mantenimientos(herramienta_id,cantidad,motivo,descripcion,responsable) VALUES(?,?,?,?,?)', [$h, (int)$c, $mo, $de, $re]);
                mov('incidencia', 'mantenimiento', (int)db()->lastInsertId(), "{$v['nombre']}: $mo ($c unidad/es)");
            });
            if (!$m) { flash('Incidencia registrada.'); go($self); }
            $err[] = $m;
        }
        flash(implode(' ', $err), false); go($self . '?a=nuevo');
    } elseif ($op === 'actualizar') {
        $id = (int)val('id'); $es = val('estado'); $re = val('resultado'); $ob = val('observaciones');
        $m = tx(function () use ($id, $es, $re, $ob) {
            $r = q('SELECT m.*, h.nombre hn FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id WHERE m.id=? FOR UPDATE', [$id])->fetch();
            if (!$r) throw new Exception('Mantenimiento inexistente.');
            if ($r['estado'] === 'finalizado') throw new Exception('El mantenimiento ya está finalizado y no puede modificarse.');
            if (strlen($ob) > 255) throw new Exception('Observaciones demasiado largas.');
            $orden = ['pendiente' => 0, 'en_proceso' => 1, 'finalizado' => 2];
            if (!isset($orden[$es]) || $orden[$es] < $orden[$r['estado']]) throw new Exception('Cambio de estado no permitido.');
            if ($es === 'finalizado') {
                if (!in_array($re, ['disponible', 'fuera_servicio'], true)) throw new Exception('Indique si la herramienta vuelve a estar disponible o queda fuera de servicio.');
                q("UPDATE mantenimientos SET estado='finalizado', resultado=?, observaciones=?, fecha_fin=NOW() WHERE id=?", [$re, $ob, $id]);
                mov('fin_mantenimiento', 'mantenimiento', $id, $r['hn'] . ': mantenimiento finalizado (' . ($re === 'disponible' ? 'vuelve a estar disponible' : 'queda fuera de servicio') . ')');
            } else {
                q('UPDATE mantenimientos SET estado=?, observaciones=? WHERE id=?', [$es, $ob, $id]);
                if ($es !== $r['estado']) mov('inicio_mantenimiento', 'mantenimiento', $id, $r['hn'] . ': mantenimiento en proceso');
            }
        });
        $m ? flash($m, false) : flash('Mantenimiento actualizado.'); go($self);
    }
}
$a = val('a');
if ($a === 'nuevo') {
    page_start('Registrar incidencia', 'mantenimiento');
    echo '<div class="box"><form class="f" method="post">' . csrf() . '<input type="hidden" name="op" value="nuevo"><label>Herramienta<select name="herramienta_id" required>';
    foreach (q('SELECT id,codigo,nombre,disponible FROM v_herramientas WHERE activo=1 AND disponible>0 ORDER BY nombre') as $h) echo '<option value="' . $h['id'] . '">' . e($h['codigo'] . ' · ' . $h['nombre'] . ' (disp. ' . $h['disponible'] . ')') . '</option>';
    echo '</select></label><label>Cantidad<input type="number" name="cantidad" min="1" value="1" required></label><label>Motivo<input name="motivo" maxlength="100" required></label><label>Responsable<input name="responsable" maxlength="100" required></label><label>Descripción<input name="descripcion" maxlength="255"></label><div><button class="btn">Registrar</button> <a class="btn g" href="' . e(app_url($self)) . '">Cancelar</a></div></form></div>';
    page_end(); exit;
}
if ($a === 'gestionar') {
    $r = q("SELECT m.*, h.nombre hn FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id WHERE m.id=? AND m.estado<>'finalizado'", [(int)val('id')])->fetch();
    if (!$r) { flash('Mantenimiento inexistente o ya finalizado.', false); go($self); }
    page_start('Gestionar mantenimiento', 'mantenimiento');
    echo '<div class="box"><p><b>' . e($r['hn']) . '</b> — ' . e($r['motivo']) . ' (' . $r['cantidad'] . ' unidad/es)<br>' . e($r['descripcion']) . '</p><form class="f" method="post">' . csrf() . '<input type="hidden" name="op" value="actualizar"><input type="hidden" name="id" value="' . $r['id'] . '"><label>Estado<select name="estado">';
    foreach (['pendiente' => 'Pendiente', 'en_proceso' => 'En proceso', 'finalizado' => 'Finalizado'] as $k => $l) echo '<option value="' . $k . '"' . ($k === $r['estado'] ? ' selected' : '') . '>' . $l . '</option>';
    echo '</select></label><label>Si finaliza, la herramienta…<select name="resultado"><option value="">(seleccione)</option><option value="disponible">Vuelve a estar disponible</option><option value="fuera_servicio">Continúa fuera de servicio</option></select></label><label>Observaciones<input name="observaciones" maxlength="255" value="' . e($r['observaciones']) . '"></label><div><button class="btn">Guardar</button> <a class="btn g" href="' . e(app_url($self)) . '">Cancelar</a></div></form></div>';
    page_end(); exit;
}
page_start('Mantenimiento e incidencias', 'mantenimiento', '<a class="btn" href="' . e(app_url($self . '?a=nuevo')) . '">+ Registrar incidencia</a>');
echo '<div class="box"><table><tr><th>#</th><th>Herramienta</th><th>Cant.</th><th>Motivo</th><th>Registro</th><th>Responsable</th><th>Estado</th><th>Observaciones</th><th></th></tr>';
$cl = ['pendiente' => 'warn', 'en_proceso' => 'info', 'finalizado' => 'ok'];
foreach (q('SELECT m.*, h.nombre hn FROM mantenimientos m JOIN herramientas h ON h.id=m.herramienta_id ORDER BY (m.estado=\'finalizado\'), m.fecha_registro DESC')->fetchAll() as $r) {
    $est = ucfirst(str_replace('_', ' ', $r['estado'])) . ($r['resultado'] ? ' · ' . str_replace('_', ' ', $r['resultado']) : '');
    echo '<tr><td>' . $r['id'] . '</td><td>' . e($r['hn']) . '</td><td>' . $r['cantidad'] . '</td><td>' . e($r['motivo']) . '<br><small>' . e($r['descripcion']) . '</small></td><td>' . e(date('d/m/Y', strtotime($r['fecha_registro']))) . '</td><td>' . e($r['responsable']) . '</td><td>' . badge([$est, $cl[$r['estado']]]) . '</td><td>' . e($r['observaciones']) . '</td><td>' . ($r['estado'] !== 'finalizado' ? '<a class="btn sm s" href="' . e(app_url($self . '?a=gestionar&id=' . (int)$r['id'])) . '">Gestionar</a>' : '') . '</td></tr>';
}
echo '</table></div>';
page_end();
