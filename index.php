<?php
// Pantalla de presentación: punto de entrada del sitio. No consulta la base de datos.
require __DIR__ . '/functions.php';
$go = app_url('inicio.php');
$arrow = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
$logo = '<svg viewBox="0 0 24 24" width="28" height="28"><path fill="#f26b1d" d="M22 19l-8.5-8.5a6 6 0 0 0-7.7-7.7l3.9 3.9-2.8 2.8-3.9-3.9a6 6 0 0 0 7.7 7.7L19 22z"/></svg>';
$feat = [
  ['dashboard', 'Inicio con indicadores', 'Total, disponibles, prestadas, en mantenimiento y fuera de servicio, con alertas de devoluciones atrasadas.'],
  ['inventario', 'Inventario completo', 'Alta, edición y baja de herramientas con categorías, búsqueda por nombre o código y filtros por estado.'],
  ['prestamos', 'Préstamos y devoluciones', 'Control de existencias en cada operación: no se presta más de lo disponible ni a personal inactivo.'],
  ['personal', 'Personal del taller', 'Registro de quién puede usar las herramientas, con sector, cargo y estado activo o inactivo.'],
  ['mantenimiento', 'Mantenimiento e incidencias', 'Seguimiento de lo dañado: pendiente, en proceso y finalizado, con el resultado de la reparación.'],
  ['reportes', 'Historial y reportes', 'Cada movimiento queda registrado. Genera reportes para imprimir o exportar a CSV.'],
];
?><!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ToolBoard · Control de herramientas para talleres industriales</title>
<link rel="stylesheet" href="<?= e(app_url('style.css')) ?>"><link rel="stylesheet" href="<?= e(app_url('landing.css')) ?>"></head>
<body class="landing">
<header class="l-nav"><div class="l-wrap"><div class="logo"><?= $logo ?> ToolBoard</div><a class="btn sm" href="<?= $go ?>">Acceder al sistema</a></div></header>
<main class="l-wrap">
  <section class="hero">
    <div>
      <span class="eyebrow">Gestión de taller industrial</span>
      <h1>Cada herramienta, <span class="grad">siempre bajo control.</span></h1>
      <p class="lead">ToolBoard es el sistema web que te dice qué herramientas tiene el taller, dónde están, quién las tiene y cuáles necesitan mantenimiento, en tiempo real.</p>
      <div class="cta"><a class="btn big" href="<?= $go ?>">Acceder al sistema <?= $arrow ?></a><a class="ghost" href="#funciones">Ver funciones</a></div>
      <div class="stats"><div><b>6</b><span>módulos integrados</span></div><div><b>100%</b><span>trazabilidad en el historial</span></div><div><b>0</b><span>servicios externos de pago</span></div></div>
    </div>
    <div class="visual" aria-hidden="true">
      <div class="ring"></div><div class="ring r2"></div>
      <div class="core"><?= str_replace('width="28" height="28"', '', $logo) ?></div>
      <div class="chip"><span class="ic"><?= ico('inventario') ?></span>Inventario</div>
      <div class="chip"><span class="ic"><?= ico('prestamos') ?></span>Préstamos</div>
      <div class="chip"><span class="ic"><?= ico('mantenimiento') ?></span>Mantenimiento</div>
      <div class="chip"><span class="ic"><?= ico('reportes') ?></span>Reportes</div>
    </div>
  </section>

  <section class="sec" id="funciones">
    <h2>Todo lo que necesita tu taller, <span class="grad">en un solo lugar</span></h2>
    <p class="sub">Destornilladores, taladros, multímetros o amoladoras: ToolBoard centraliza el inventario y todo lo que les ocurre.</p>
    <div class="grid">
      <?php foreach ($feat as $i => $f): ?>
      <article class="feat" data-rv><div class="ico"><?= ico($f[0]) ?></div><h3><?= e($f[1]) ?></h3><p><?= e($f[2]) ?></p></article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="sec">
    <h2>Por qué <span class="grad">ToolBoard</span></h2>
    <p class="sub">Menos pérdidas, menos tiempo buscando y decisiones basadas en datos reales.</p>
    <div class="grid">
      <article class="feat" data-rv><h3>Disponibilidad exacta</h3><p>Las unidades disponibles se calculan a partir de los préstamos y mantenimientos reales: nunca quedan desactualizadas.</p></article>
      <article class="feat" data-rv><h3>Nada se pierde</h3><p>El historial conserva préstamos, devoluciones, incidencias y bajas, incluso si se elimina el registro original.</p></article>
      <article class="feat" data-rv><h3>Operaciones seguras</h3><p>Transacciones, validaciones en el servidor, consultas preparadas y protección contra ataques comunes.</p></article>
    </div>
  </section>

  <section class="sec">
    <h2>Empieza en <span class="grad">tres pasos</span></h2>
    <div class="grid" style="margin-top:28px">
      <article class="feat step" data-rv><div class="n grad">01</div><h3>Registra</h3><p>Carga las herramientas, sus categorías y al personal autorizado.</p></article>
      <article class="feat step" data-rv><div class="n grad">02</div><h3>Presta y recibe</h3><p>Registra cada préstamo y devolución indicando el estado en que vuelve la herramienta.</p></article>
      <article class="feat step" data-rv><div class="n grad">03</div><h3>Controla</h3><p>Revisa las alertas en Inicio y consulta los reportes cuando los necesites.</p></article>
    </div>
  </section>

  <section class="band" data-rv>
    <h2>¿Listo para ordenar tu taller?</h2><p>Entra al sistema y empieza a controlar tus herramientas ahora mismo.</p>
    <a class="btn big" href="<?= $go ?>">Acceder al sistema <?= $arrow ?></a>
  </section>
</main>
<footer class="l-foot">ToolBoard · Sistema de gestión y control de herramientas de un taller industrial</footer>
<script src="<?= e(app_url('app.js')) ?>"></script>
</body></html>
