<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('America/Argentina/Buenos_Aires');

function db(): PDO {
    static $p = null;
    if (!$p) {
        try {
            $p = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false]);
        } catch (PDOException $x) {
            http_response_code(500);
            exit('No se pudo conectar a la base de datos. Revise db.php e importe database/toolboard.sql.');
        }
    }
    return $p;
}
function q(string $s, array $p = []): PDOStatement { $st = db()->prepare($s); $st->execute($p); return $st; }
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function val(string $k, string $d = ''): string { $v = $_POST[$k] ?? $_GET[$k] ?? $d; return is_array($v) ? $d : trim((string)$v); }
function app_url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function csrf(): string {
    if (empty($_SESSION['t'])) $_SESSION['t'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="_t" value="' . $_SESSION['t'] . '">';
}
function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['t'] ?? '', $_POST['_t'] ?? '')) {
        http_response_code(400); exit('Token de seguridad inválido. Recargue la página e intente de nuevo.');
    }
}
csrf_check();
function flash(string $m, bool $ok = true): void { $_SESSION['f'] = [$m, $ok]; }
function go(string $u): void { header('Location: ' . app_url($u)); exit; }
function mov(string $tipo, string $ent, ?int $id, string $desc): void {
    q('INSERT INTO movimientos(tipo,entidad,entidad_id,descripcion) VALUES(?,?,?,?)', [$tipo, $ent, $id, $desc]);
}
/** Ejecuta $fn en una transacción. Devuelve null si todo salió bien o el mensaje de error. */
function tx(callable $fn): ?string {
    $p = db(); $p->beginTransaction();
    try { $fn(); $p->commit(); return null; }
    catch (PDOException $x) { $p->rollBack(); return 'Error de base de datos: la operación no se realizó.'; }
    catch (Exception $x) { $p->rollBack(); return $x->getMessage(); }
}
function fecha_ok(string $s) {
    $d = DateTime::createFromFormat('Y-m-d\TH:i', $s);
    return ($d && $d->format('Y-m-d\TH:i') === $s) ? $d->format('Y-m-d H:i:s') : false;
}
function estado_herr(array $r): array {
    if ($r['disponible'] > 0) return ['Disponible', 'ok'];
    if ($r['en_mant'] > 0) return ['En mantenimiento', 'info'];
    if ($r['prestadas'] > 0) return ['Prestada', 'warn'];
    return ['Fuera de servicio', 'bad'];
}
function badge(array $b): string { return '<span class="badge ' . $b[1] . '">' . e($b[0]) . '</span>'; }
function page_start(string $titulo, string $act, string $acciones = ''): void {
    $nav = ['dashboard' => ['Inicio', '/inicio.php', '▦'], 'inventario' => ['Inventario', '/inventario.php', '🔧'],
        'prestamos' => ['Préstamos y devoluciones', '/prestamos.php', '⇄'], 'personal' => ['Personal', '/personal.php', '👤'],
        'mantenimiento' => ['Mantenimiento', '/mantenimiento.php', '⚠'], 'historial' => ['Historial', '/historial.php', '🕘'],
        'reportes' => ['Reportes', '/reportes.php', '🖨']];
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($titulo) . ' · ToolBoard</title><link rel="stylesheet" href="' . app_url('style.css') . '"></head><body>';
    echo '<aside id="side" class="no-print"><div class="logo"><svg viewBox="0 0 24 24" width="28" height="28"><path fill="#f26b1d" d="M22 19l-8.5-8.5a6 6 0 0 0-7.7-7.7l3.9 3.9-2.8 2.8-3.9-3.9a6 6 0 0 0 7.7 7.7L19 22z"/></svg> ToolBoard</div><nav>';
    foreach ($nav as $k => $n) echo '<a href="' . app_url($n[1]) . '" class="' . ($k === $act ? 'on' : '') . '"><span class="ic">' . ico($k) . '</span><em>' . e($n[0]) . '</em></a>';
    echo '</nav><a class="back" href="' . app_url('index.php') . '">← Presentación</a></aside><main><header class="top no-print"><button id="menu" aria-label="Menú">☰</button><div><small>ToolBoard</small><h1>' . e($titulo) . '</h1></div><div class="acts">' . $acciones . '</div></header><section class="content">';
    if (!empty($_SESSION['f'])) { echo '<div class="alert ' . ($_SESSION['f'][1] ? 'ok' : 'bad') . '">' . e($_SESSION['f'][0]) . '</div>'; unset($_SESSION['f']); }
}
function page_end(): void { echo '</section></main><script src="' . app_url('app.js') . '"></script></body></html>'; }

function ico(string $k): string {
    $d = ['dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'inventario' => '<path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'prestamos' => '<path d="M4 8h14l-3-3M20 16H6l3 3"/>',
        'personal' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'mantenimiento' => '<path d="M14.7 6.3a4 4 0 0 0-5 5L3 18l3 3 6.7-6.7a4 4 0 0 0 5-5l-2.5 2.5-2.2-.5-.5-2.2z"/>',
        'historial' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'reportes' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>'];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($d[$k] ?? '') . '</svg>';
}
