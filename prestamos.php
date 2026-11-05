<?php
require __DIR__ . '/functions.php';
$self = '/prestamos.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = val('op');
    if ($op === 'nuevo') {
        $h = (int)val('herramienta_id'); $pe = (int)val('personal_id'); $c = val('cantidad'); $fp = fecha_ok(val('fecha_prevista')); $obs = val('observaciones'); $err = [];
        if (!ctype_digit($c) || (int)$c < 1) $err[] = 'La cantidad debe ser un entero de al menos 1.';
        if (!$fp) $err[] = 'La fecha prevista de devolución no es válida.'; elseif (strtotime($fp) <= time()) $err[] = 'La fecha prevista debe ser posterior a este momento.';
        if (strlen($obs) > 255) $err[] = 'Observaciones demasiado largas.';
        if (!$err) {
            $m = tx(function () use ($h, $pe, $c, $fp, $obs) {
                if (!q('SELECT id FROM herramientas WHERE id=? AND activo=1 FOR UPDATE', [$h])->fetch()) throw new Exception('Herramienta inválida o dada de baja.');
                $per = q('SELECT * FROM personal WHERE id=?', [$pe])->fetch();
                if (!$per) throw new Exception('Persona inexistente.');
                if (!$per['activo']) throw new Exception('La persona está inactiva y no puede recibir préstamos.');
                $v = q('SELECT nombre, disponible FROM v_herramientas WHERE id=?', [$h])->fetch();
                if ((int)$c > $v['disponible']) throw new Exception('Solo hay ' . $v['disponible'] . ' unidad(es) disponible(s) de ' . $v['nombre'] . '.');
                q('INSERT INTO prestamos(herramienta_id,personal_id,cantidad,fecha_prevista,observaciones) VALUES(?,?,?,?,?)', [$h, $pe, (int)$c, $fp, $obs]);
                mov('prestamo', 'prestamo', (int)db()->lastInsertId(), "Préstamo de $c x {$v['nombre']} a {$per['nombre']} {$per['apellido']}");
            });
            if (!$m) { flash('Préstamo registrado.'); go($self); }
            $err[] = $m;
        }
        flash(implode(' ', $err), false); go($self . '?a=nuevo');
    } elseif ($op === 'devolver') {
        $id = (int)val('id'); $cond = val('condicion'); $obs = val('observaciones');
        if (!in_array($cond, ['buena', 'danada', 'mantenimiento'], true) || strlen($obs) > 200) { flash('Datos de devolución inválidos.', false); go($self); }
        $m = tx(function () use ($id, $cond, $obs) {
            $p = q("SELECT p.*, h.nombre hn, CONCAT(pe.nombre,' ',pe.apellido) persona FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal pe ON pe.id=p.personal_id WHERE p.id=? FOR UPDATE", [$id])->fetch();
            if (!$p || $p['estado'] !== 'activo') throw new Exception('El préstamo no existe o ya fue devuelto.');
            q("UPDATE prestamos SET estado='devuelto', fecha_devolucion=NOW(), observaciones=? WHERE id=?", [trim($p['observaciones'] . ' ' . $obs), $id]);
            $lbl = ['buena' => 'en buen estado', 'danada' => 'dañada', 'mantenimiento' => 'con necesidad de mantenimiento'][$cond];
            mov('devolucion', 'prestamo', $id, "Devolución {$lbl}: {$p['cantidad']} x {$p['hn']} ({$p['persona']})");
            if ($cond !== 'buena') {
                $motivo = $cond === 'danada' ? 'Daño detectado en devolución' : 'Mantenimiento requerido en devolución';
                q('INSERT INTO mantenimientos(herramienta_id,cantidad,motivo,descripcion,responsable) VALUES(?,?,?,?,?)', [$p['herramienta_id'], $p['cantidad'], $motivo, $obs, $p['persona']]);
                mov('incidencia', 'mantenimiento', (int)db()->lastInsertId(), "{$p['hn']}: $motivo");
            }
        });
        $m ? flash($m, false) : flash('Devolución registrada.'); go($self);
    }
}
$a = val('a');
$nuevo = '<a class="btn" href="' . e(app_url($self . '?a=nuevo')) . '">+ Nuevo préstamo</a>';
if ($a === 'nuevo') {
    page_start('Nuevo préstamo', 'prestamos');
    echo '<div class="box"><form class="f" method="post">' . csrf() . '<input type="hidden" name="op" value="nuevo"><label>Herramienta<select name="herramienta_id" required>';
    foreach (q('SELECT id,codigo,nombre,disponible FROM v_herramientas WHERE activo=1 AND disponible>0 ORDER BY nombre')->fetchAll() as $h) echo '<option value="' . $h['id'] . '">' . e($h['codigo'] . ' · ' . $h['nombre'] . ' (disp. ' . $h['disponible'] . ')') . '</option>';
    echo '</select></label><label>Persona responsable<select name="personal_id" required>';
    foreach (q('SELECT * FROM personal WHERE activo=1 ORDER BY apellido') as $p) echo '<option value="' . $p['id'] . '">' . e($p['apellido'] . ', ' . $p['nombre'] . ' (' . $p['sector'] . ')') . '</option>';
    echo '</select></label><label>Cantidad<input type="number" name="cantidad" min="1" value="1" required></label><label>Fecha prevista de devolución<input type="datetime-local" name="fecha_prevista" required value="' . date('Y-m-d\TH:i', strtotime('+1 day')) . '"></label><label>Observaciones<input name="observaciones" maxlength="255"></label><div><button class="btn">Registrar préstamo</button> <a class="btn g" href="' . e(app_url($self)) . '">Cancelar</a></div></form></div>';
    page_end(); exit;
}
if ($a === 'devolver') {
    $p = q("SELECT p.*, h.nombre hn, CONCAT(pe.nombre,' ',pe.apellido) persona FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal pe ON pe.id=p.personal_id WHERE p.id=? AND p.estado='activo'", [(int)val('id')])->fetch();
    if (!$p) { flash('Préstamo inexistente o ya devuelto.', false); go($self); }
    page_start('Registrar devolución', 'prestamos');
    echo '<div class="box"><p><b>' . e($p['cantidad'] . ' x ' . $p['hn']) . '</b> — ' . e($p['persona']) . '</p><form class="f" method="post">' . csrf() . '<input type="hidden" name="op" value="devolver"><input type="hidden" name="id" value="' . $p['id'] . '"><label>Estado al devolver<select name="condicion"><option value="buena">Buen estado</option><option value="danada">Dañada</option><option value="mantenimiento">Necesita mantenimiento</option></select></label><label>Observaciones<input name="observaciones" maxlength="200"></label><div><button class="btn">Confirmar devolución</button> <a class="btn g" href="' . e(app_url($self)) . '">Cancelar</a></div></form></div>';
    page_end(); exit;
}
$f = val('estado'); $w = ['activo' => "p.estado='activo'", 'atrasado' => "p.estado='activo' AND p.fecha_prevista<NOW()", 'devuelto' => "p.estado='devuelto'"][$f] ?? '1';
page_start('Préstamos y devoluciones', 'prestamos', $nuevo);
echo '<div class="box"><form class="filters" method="get"><label>Mostrar<select name="estado">';
foreach (['' => 'Todos', 'activo' => 'Activos', 'atrasado' => 'Atrasados', 'devuelto' => 'Devueltos'] as $k => $l) echo '<option value="' . $k . '"' . ($k === $f ? ' selected' : '') . '>' . $l . '</option>';
echo '</select></label><button class="btn s">Filtrar</button></form><table><tr><th>#</th><th>Herramienta</th><th>Persona</th><th>Cant.</th><th>Préstamo</th><th>Prevista</th><th>Devolución</th><th>Estado</th><th></th></tr>';
foreach (q("SELECT p.*, h.nombre hn, CONCAT(pe.apellido,', ',pe.nombre) persona, (p.estado='activo' AND p.fecha_prevista<NOW()) atr FROM prestamos p JOIN herramientas h ON h.id=p.herramienta_id JOIN personal pe ON pe.id=p.personal_id WHERE $w ORDER BY p.id DESC")->fetchAll() as $r) {
    $b = $r['estado'] === 'devuelto' ? ['Devuelto', 'ok'] : ($r['atr'] ? ['Atrasado', 'bad'] : ['Activo', 'warn']);
    echo '<tr><td>' . $r['id'] . '</td><td>' . e($r['hn']) . '</td><td>' . e($r['persona']) . '</td><td>' . $r['cantidad'] . '</td><td>' . e(date('d/m/Y H:i', strtotime($r['fecha_prestamo']))) . '</td><td>' . e(date('d/m/Y H:i', strtotime($r['fecha_prevista']))) . '</td><td>' . ($r['fecha_devolucion'] ? e(date('d/m/Y H:i', strtotime($r['fecha_devolucion']))) : '—') . '</td><td>' . badge($b) . '</td><td>' . ($r['estado'] === 'activo' ? '<a class="btn sm s" href="' . e(app_url($self . '?a=devolver&id=' . (int)$r['id'])) . '">Devolver</a>' : '') . '</td></tr>';
}
echo '</table></div>';
page_end();
