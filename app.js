document.documentElement.classList.add('js');
var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
var sd = document.getElementById('side'), mb = document.getElementById('menu');
if (mb) mb.addEventListener('click', function () { sd.classList.toggle('open'); });
document.addEventListener('click', function (e) { if (sd && sd.classList.contains('open') && !sd.contains(e.target) && e.target !== mb) sd.classList.remove('open'); });

/* Ventana de confirmación propia (reemplaza a confirm()) */
function ask(msg, ok) {
  var m = document.createElement('div'); m.className = 'modal'; m.setAttribute('role', 'dialog'); m.setAttribute('aria-modal', 'true');
  m.innerHTML = '<div><h3>Confirmar acción</h3><p></p><div class="row"><button type="button" class="btn g">Cancelar</button><button type="button" class="btn d">Confirmar</button></div></div>';
  m.querySelector('p').textContent = msg;
  var b = m.querySelectorAll('button'), prev = document.activeElement;
  function close() { m.remove(); document.removeEventListener('keydown', key); if (prev) prev.focus(); }
  function key(e) { if (e.key === 'Escape') close(); }
  b[0].onclick = close; b[1].onclick = function () { close(); ok(); };
  m.addEventListener('click', function (e) { if (e.target === m) close(); });
  document.addEventListener('keydown', key); document.body.appendChild(m); b[0].focus();
}
document.addEventListener('submit', function (ev) {
  var f = ev.target, msg = f.getAttribute('data-confirm');
  if (msg && !f.dataset.ok) { ev.preventDefault(); ask(msg, function () { f.dataset.ok = '1'; busy(f); f.submit(); }); return; }
  busy(f);
});
function busy(f) { var s = f.querySelector('button.btn:not([type=button])'); if (s) setTimeout(function () { s.classList.add('busy'); }, 0); }
window.addEventListener('pageshow', function () { document.querySelectorAll('.btn.busy').forEach(function (b) { b.classList.remove('busy'); }); });

/* Avisos: se desvanecen solos */
document.querySelectorAll('.alert').forEach(function (a) { setTimeout(function () { a.classList.add('out'); setTimeout(function () { a.remove(); }, 600); }, 6000); });

/* Revelado al cargar y al desplazarse */
var els = document.querySelectorAll('.card,.box,[data-rv]');
els.forEach(function (el, i) { el.classList.add('rv'); el.style.setProperty('--d', Math.min(i * 0.06, 0.4) + 's'); });
if ('IntersectionObserver' in window && !calm) {
  var io = new IntersectionObserver(function (es) { es.forEach(function (x) { if (x.isIntersecting) { x.target.classList.add('in'); io.unobserve(x.target); } }); }, { threshold: 0.06 });
  els.forEach(function (el) { io.observe(el); });
} else { els.forEach(function (el) { el.classList.add('in'); }); }

/* Contadores animados del dashboard */
document.querySelectorAll('[data-count]').forEach(function (n) {
  var to = parseInt(n.getAttribute('data-count'), 10) || 0;
  if (calm || to === 0) { n.textContent = to; return; }
  var t0 = null; n.textContent = '0';
  requestAnimationFrame(function step(t) { if (!t0) t0 = t; var p = Math.min((t - t0) / 900, 1); n.textContent = Math.round(to * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); });
});
