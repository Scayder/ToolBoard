<?php
require __DIR__ . '/functions.php';
$self = '/inventario.php';
$form = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $op = val('op');
    if ($op === 'guardar') {
        $id = (int)val('id'); $cod = val('codigo'); $nom = val('nombre'); $cat = (int)val('categoria_id'); $tot = val('cantidad_total'); $err = [];
        if ($cod === '' || strlen($cod) > 30) $err[] = 'El código es obligatorio (máx. 30 caracteres).';
        if ($nom === '' || strlen($nom) > 100) $err[] = 'El nombre es obligatorio (máx. 100 caracteres).';
        if (!q('SELECT 1 FROM categorias WHERE id=?', [$cat])->fetch()) $err[] = 'Categoría inválida.';
        if (!ctype_digit($tot) || strlen($tot) > 6) $err[] = 'La cantidad total debe ser un entero mayor o igual a 0.';
        if (strlen(val('descripcion')) > 255 || strlen(val('marca')) > 60 || strlen(val('ubicacion')) > 80) $err[] = 'Algún campo supera el largo permitido.';
        if (q('SELECT 1 FROM herramientas WHERE codigo=? AND id<>?', [$cod, $id])->fetch()) $err[] = 'Ya existe una herramienta con ese código.';
        if ($id && !$err) {
            $u = q('SELECT prestadas+en_mant+fuera u FROM v_herramientas WHERE id=?', [$id])->fetchColumn();
            if ((int)$tot < (int)$u) $err[] = "No puede ser menor a $u: hay unidades prestadas, en mantenimiento o fuera de servicio.";
        }
        if (!$err) {
            $d = [$cod, $nom, $cat, val('descripcion'), val('marca'), (int)$tot, val('ubicacion')];
            if ($id) { q('UPDATE herramientas SET codigo=?,nombre=?,categoria_id=?,descripcion=?,marca=?,cantidad_total=?,ubicacion=? WHERE id=?', array_merge($d, [$id])); mov('modificacion_inventario', 'herramienta', $id, "Se modificó $nom ($cod)"); }
            else { q('INSERT INTO herramientas(codigo,nombre,categoria_id,descripcion,marca,cantidad_total,ubicacion) VALUES(?,?,?,?,?,?,?)', $d); mov('alta_herramienta', 'herramienta', (int)db()->lastInsertId(), "Alta de $nom ($cod), $tot unidades"); }
            flash('Herramienta guardada.'); go($self);
        }
        flash(implode(' ', $err), false); $form = $_POST;
    } elseif ($op === 'eliminar') {
        $id = (int)val('id'); $h = q('SELECT * FROM v_herramientas WHERE id=?', [$id])->fetch();
        if (!$h) { flash('Herramienta inexistente.', false); go($self); }
        if ($h['prestadas'] > 0 || $h['en_mant'] > 0) { flash('No se puede eliminar: tiene préstamos activos o mantenimientos abiertos.', false); go($self); }
        $usos = (int)q('SELECT (SELECT COUNT(*) FROM prestamos WHERE herramienta_id=?)+(SELECT COUNT(*) FROM mantenimientos WHERE herramienta_id=?)', [$id, $id])->fetchColumn();
        if ($usos > 0) { q('UPDATE herramientas SET activo=0 WHERE id=?', [$id]); flash('Tiene historial: se dio de baja (baja lógica) y se conservan sus registros.'); }
        else { q('DELETE FROM herramientas WHERE id=?', [$id]); flash('Herramienta eliminada.'); }
        mov('baja_herramienta', 'herramienta', $id, 'Baja de ' . $h['nombre'] . ' (' . $h['codigo'] . ')'); go($self);
    } elseif ($op === 'cat_nueva') {
        $n = val('nombre');
        if ($n === '' || strlen($n) > 60) flash('Nombre de categoría inválido.', false);
        elseif (q('SELECT 1 FROM categorias WHERE nombre=?', [$n])->fetch()) flash('Esa categoría ya existe.', false);
        else { q('INSERT INTO categorias(nombre) VALUES(?)', [$n]); mov('alta_categoria', 'categoria', (int)db()->lastInsertId(), "Nueva categoría $n"); flash('Categoría creada.'); }
        go($self);
    } elseif ($op === 'cat_borrar') {
        $id = (int)val('id');
        if (q('SELECT 1 FROM herramientas WHERE categoria_id=?', [$id])->fetch()) flash('La categoría tiene herramientas asociadas.', false);
        else { q('DELETE FROM categorias WHERE id=?', [$id]); mov('baja_categoria', 'categoria', $id, 'Categoría eliminada'); flash('Categoría eliminada.'); }
        go($self);
    }
}
$cats = q('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
if (val('a') === 'form' && !$form && (int)val('id')) $form = q('SELECT * FROM v_herramientas WHERE id=?', [(int)val('id')])->fetch() ?: null;
$showForm = $form !== null || val('a') === 'form';
page_start('Inventario de herramientas', 'inventario', '<a class="btn" href="' . e(app_url($self . '?a=form')) . '">+ Nueva herramienta</a>');
if ($showForm) {
    $f = $form ?: ['id' => 0, 'codigo' => '', 'nombre' => '', 'categoria_id' => '', 'descripcion' => '', 'marca' => '', 'cantidad_total' => 1, 'ubicacion' => ''];
    echo '<div class="box"><h2>' . ($f['id'] ? 'Editar' : 'Nueva') . ' herramienta</h2><form class="f" method="post">' . csrf() . '<input type="hidden" name="op" value="guardar"><input type="hidden" name="id" value="' . (int)$f['id'] . '">';
    echo '<label>Código<input name="codigo" maxlength="30" required value="' . e($f['codigo']) . '"></label><label>Nombre<input name="nombre" maxlength="100" required value="' . e($f['nombre']) . '"></label><label>Categoría<select name="categoria_id" required>';
    foreach ($cats as $c) echo '<option value="' . $c['id'] . '"' . ($c['id'] == $f['categoria_id'] ? ' selected' : '') . '>' . e($c['nombre']) . '</option>';
    echo '</select></label><label>Marca<input name="marca" maxlength="60" value="' . e($f['marca']) . '"></label><label>Cantidad total<input type="number" min="0" name="cantidad_total" required value="' . e($f['cantidad_total']) . '"></label><label>Ubicación<input name="ubicacion" maxlength="80" value="' . e($f['ubicacion']) . '"></label><label>Descripción<input name="descripcion" maxlength="255" value="' . e($f['descripcion']) . '"></label>';
    echo '<div><button class="btn">Guardar</button> <a class="btn g" href="' . e(app_url($self)) . '">Cancelar</a></div></form></div>';
}
$qs = val('q'); $cf = (int)val('cat'); $ef = val('estado'); $w = ['activo=1']; $p = [];
if ($qs !== '') { $w[] = '(nombre LIKE ? OR codigo LIKE ?)'; $p[] = "%$qs%"; $p[] = "%$qs%"; }
if ($cf) { $w[] = 'categoria_id=?'; $p[] = $cf; }
$ew = ['disponible' => 'disponible>0', 'prestada' => 'prestadas>0', 'mantenimiento' => 'en_mant>0', 'fuera' => 'fuera>0'];
if (isset($ew[$ef])) $w[] = $ew[$ef];
$rows = q('SELECT * FROM v_herramientas WHERE ' . implode(' AND ', $w) . ' ORDER BY nombre', $p)->fetchAll();
echo '<div class="box"><form class="filters" method="get"><label>Buscar<input name="q" placeholder="Nombre o código" value="' . e($qs) . '"></label><label>Categoría<select name="cat"><option value="0">Todas</option>';
foreach ($cats as $c) echo '<option value="' . $c['id'] . '"' . ($c['id'] == $cf ? ' selected' : '') . '>' . e($c['nombre']) . '</option>';
echo '</select></label><label>Estado<select name="estado"><option value="">Todos</option>';
foreach (['disponible' => 'Con disponibles', 'prestada' => 'Con prestadas', 'mantenimiento' => 'En mantenimiento', 'fuera' => 'Fuera de servicio'] as $k => $l) echo '<option value="' . $k . '"' . ($k === $ef ? ' selected' : '') . '>' . $l . '</option>';
echo '</select></label><button class="btn s">Filtrar</button></form><table><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Ubicación</th><th>Total</th><th>Disp.</th><th>Estado</th><th></th></tr>';
foreach ($rows as $r) {
    echo '<tr><td>' . e($r['codigo']) . '</td><td>' . e($r['nombre']) . '<br><small>' . e($r['marca']) . '</small></td><td>' . e($r['categoria']) . '</td><td>' . e($r['ubicacion']) . '</td><td>' . $r['cantidad_total'] . '</td><td>' . $r['disponible'] . '</td><td>' . badge(estado_herr($r)) . '</td><td><a class="btn sm s" href="' . e(app_url($self . '?a=form&id=' . (int)$r['id'])) . '">Editar</a> ';
    echo '<form class="inline" method="post" data-confirm="¿Eliminar esta herramienta?">' . csrf() . '<input type="hidden" name="op" value="eliminar"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="btn sm d">Eliminar</button></form></td></tr>';
}
if (!$rows) echo '<tr><td colspan="8">Sin resultados.</td></tr>';
echo '</table></div><div class="box"><h2>Categorías</h2><table>';
foreach ($cats as $c) echo '<tr><td>' . e($c['nombre']) . '</td><td><form class="inline" method="post" data-confirm="¿Eliminar la categoría?">' . csrf() . '<input type="hidden" name="op" value="cat_borrar"><input type="hidden" name="id" value="' . $c['id'] . '"><button class="btn sm d">Eliminar</button></form></td></tr>';
echo '</table><form class="filters" method="post" style="margin-top:12px">' . csrf() . '<input type="hidden" name="op" value="cat_nueva"><label>Nueva categoría<input name="nombre" maxlength="60" required></label><button class="btn s">Agregar</button></form></div>';
page_end();
