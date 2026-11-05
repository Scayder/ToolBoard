<?php
require __DIR__ . '/functions.php';
$self = '/personal.php'; $form = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = val('op'); $id = (int)val('id');
    if ($op === 'guardar') {
        $n = val('nombre'); $a = val('apellido'); $d = val('documento'); $s = val('sector'); $c = val('cargo'); $act = val('activo') === '1' ? 1 : 0; $err = [];
        foreach (['Nombre' => $n, 'Apellido' => $a, 'Documento/código' => $d, 'Sector' => $s, 'Cargo' => $c] as $k => $v) if ($v === '' || strlen($v) > 60) $err[] = "$k es obligatorio (máx. 60 caracteres).";
        if (q('SELECT 1 FROM personal WHERE documento=? AND id<>?', [$d, $id])->fetch()) $err[] = 'Ya existe una persona con ese documento/código.';
        if (!$err) {
            if ($id) { q('UPDATE personal SET nombre=?,apellido=?,documento=?,sector=?,cargo=?,activo=? WHERE id=?', [$n, $a, $d, $s, $c, $act, $id]); mov('modificacion_personal', 'personal', $id, "Se modificó a $n $a"); }
            else { q('INSERT INTO personal(nombre,apellido,documento,sector,cargo,activo) VALUES(?,?,?,?,?,?)', [$n, $a, $d, $s, $c, $act]); mov('alta_personal', 'personal', (int)db()->lastInsertId(), "Alta de $n $a"); }
            flash('Persona guardada.'); go($self);
        }
        flash(implode(' ', $err), false); $form = $_POST;
    } elseif ($op === 'eliminar') {
        $p = q('SELECT * FROM personal WHERE id=?', [$id])->fetch();
        if (!$p) { flash('Persona inexistente.', false); go($self); }
        if (q("SELECT 1 FROM prestamos WHERE personal_id=? AND estado='activo'", [$id])->fetch()) flash('Tiene préstamos activos: registre primero la devolución.', false);
        elseif (q('SELECT 1 FROM prestamos WHERE personal_id=?', [$id])->fetch()) { q('UPDATE personal SET activo=0 WHERE id=?', [$id]); mov('baja_personal', 'personal', $id, 'Inactivada ' . $p['nombre'] . ' ' . $p['apellido']); flash('Tiene historial: se inactivó y se conserva su historial.'); }
        else { q('DELETE FROM personal WHERE id=?', [$id]); mov('baja_personal', 'personal', $id, 'Eliminada ' . $p['nombre'] . ' ' . $p['apellido']); flash('Persona eliminada.'); }
        go($self);
    }
}
if (val('a') === 'form' && !$form && (int)val('id')) $form = q('SELECT * FROM personal WHERE id=?', [(int)val('id')])->fetch() ?: null;
page_start('Personal del taller', 'personal', '<a class="btn" href="' . e(app_url($self . '?a=form')) . '">+ Nueva persona</a>');
if ($form !== null || val('a') === 'form') {
    $f = $form ?: ['id' => 0, 'nombre' => '', 'apellido' => '', 'documento' => '', 'sector' => '', 'cargo' => '', 'activo' => 1];
    echo '<div class="box"><h2>' . ($f['id'] ? 'Editar' : 'Nueva') . ' persona</h2><form class="f" method="post">' . csrf() . '<input type="hidden" name="op" value="guardar"><input type="hidden" name="id" value="' . (int)$f['id'] . '">';
    foreach (['nombre' => 'Nombre', 'apellido' => 'Apellido', 'documento' => 'Documento o código interno', 'sector' => 'Sector', 'cargo' => 'Cargo'] as $k => $l) echo '<label>' . $l . '<input name="' . $k . '" maxlength="60" required value="' . e($f[$k]) . '"></label>';
    echo '<label>Estado<select name="activo"><option value="1"' . ($f['activo'] ? ' selected' : '') . '>Activo</option><option value="0"' . ($f['activo'] ? '' : ' selected') . '>Inactivo</option></select></label><div><button class="btn">Guardar</button> <a class="btn g" href="' . e(app_url($self)) . '">Cancelar</a></div></form></div>';
}
$qs = val('q'); $w = '1'; $p = [];
if ($qs !== '') { $w = '(nombre LIKE ? OR apellido LIKE ? OR documento LIKE ?)'; $p = ["%$qs%", "%$qs%", "%$qs%"]; }
echo '<div class="box"><form class="filters" method="get"><label>Buscar<input name="q" value="' . e($qs) . '"></label><button class="btn s">Buscar</button></form><table><tr><th>Nombre</th><th>Documento</th><th>Sector</th><th>Cargo</th><th>Estado</th><th></th></tr>';
foreach (q("SELECT * FROM personal WHERE $w ORDER BY apellido, nombre", $p)->fetchAll() as $r) {
    echo '<tr><td>' . e($r['apellido'] . ', ' . $r['nombre']) . '</td><td>' . e($r['documento']) . '</td><td>' . e($r['sector']) . '</td><td>' . e($r['cargo']) . '</td><td>' . badge($r['activo'] ? ['Activo', 'ok'] : ['Inactivo', 'muted']) . '</td><td><a class="btn sm s" href="' . e(app_url($self . '?a=form&id=' . (int)$r['id'])) . '">Editar</a> <form class="inline" method="post" data-confirm="¿Eliminar a esta persona?">' . csrf() . '<input type="hidden" name="op" value="eliminar"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="btn sm d">Eliminar</button></form></td></tr>';
}
echo '</table></div>';
page_end();
