/* Unsent · js/effects.js · back-to-top, greeting, backdrop. Loaded in order by includes/footer.php (core.js first). */
/* global $, CSRF, post, Swal */
/* Unsent · calm extras: back-to-top button only.
   (The old tilt / ripple / drifting sheets / parallax layer is gone: it fought fx3d.css and the
   "calm motion" rule. Number count-up and scroll reveal live in fx3d.js.) */
(() => {
  const calm = matchMedia("(prefers-reduced-motion: reduce)").matches || document.documentElement.hasAttribute("data-calm");
  const up = document.createElement("button");
  up.type = "button";
  up.className = "fx-top";
  up.setAttribute("aria-label", "Back to top");
  up.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
  up.onclick = () => scrollTo({ top: 0, behavior: calm ? "auto" : "smooth" });
  document.body.append(up);
  let tick = false;
  addEventListener("scroll", () => {
    if (tick) return;
    tick = true;
    requestAnimationFrame(() => {
      up.classList.toggle("on", scrollY > 600);
      tick = false;
    });
  }, { passive: true });
})();

/* greeting follows the visitor's own clock */
(() => {
  const g = document.querySelector("[data-greet]");
  if (!g) return;
  const h = new Date().getHours();
  g.textContent = h < 5 ? "Still up" : h < 12 ? "Good morning" : h < 18 ? "Good afternoon" : "Good evening";
})();


/* 3D backdrop: drifting paper sheets + soft pointer parallax. Skipped in admin, calm mode and reduced motion. */
(() => {
  const root = document.documentElement;
  if (document.querySelector(".app.adm")) return;
  if (matchMedia("(prefers-reduced-motion: reduce)").matches || root.hasAttribute("data-calm")) return;
  const mk = (tag, cls) => { const n = document.createElement(tag); n.className = cls; return n; };
  const rnd = (a, b) => a + Math.random() * (b - a);
  const scene = mk("div", "fx-scene"), stage = mk("div", "fx-stage");
  scene.setAttribute("aria-hidden", "true");
  const N = innerWidth < 900 ? 11 : 20;
  for (let i = 0; i < N; i++) {
    const s = mk("i", "fx-sheet" + ["", " y", " k", " p", " b"][i % 5]);
    s.style.cssText = `--x:${rnd(2, 94).toFixed(1)}%;--y:${rnd(4, 88).toFixed(1)}%;--z:${rnd(-380, 220).toFixed(0)}px;--d:${rnd(16, 32).toFixed(1)}s;--r:${rnd(-30, 30).toFixed(0)}deg;--s:${rnd(0.55, 1.45).toFixed(2)};animation-delay:${-rnd(0, 20).toFixed(1)}s`;
    stage.append(s);
  }
  scene.append(stage);
  document.body.prepend(scene);
  if (!matchMedia("(hover: hover) and (pointer: fine)").matches) return;
  let tx = 0, ty = 0, cx = 0, cy = 0, on = false;
  const loop = () => {
    cx += (tx - cx) * 0.07; cy += (ty - cy) * 0.07;
    stage.style.setProperty("--px", cx.toFixed(2) + "deg");
    stage.style.setProperty("--py", cy.toFixed(2) + "deg");
    if (Math.abs(tx - cx) + Math.abs(ty - cy) > 0.02) requestAnimationFrame(loop); else on = false;
  };
  addEventListener("pointermove", (e) => {
    tx = (e.clientX / innerWidth - 0.5) * 14;
    ty = -(e.clientY / innerHeight - 0.5) * 10;
    if (!on) { on = true; requestAnimationFrame(loop); }
  }, { passive: true });
})();
