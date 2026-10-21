/* fx3d.js — 3D layer for Unsent (ES module, additive: nothing else depends on it).
   1) Three.js scenes: [data-scene=envelope] an envelope that opens slowly (hover card, or proximity with data-reach=near)
   2) number count-up
   Calm on purpose: no tilt, no ripples, no parallax, no idle bounce.
   Respects body.calm, prefers-reduced-motion and missing WebGL. */

const THREE_URL = 'https://cdn.jsdelivr.net/npm/three@0.169.0/build/three.module.min.js';
const doc = typeof document !== 'undefined' ? document : null;
const calm = () => !!doc && (doc.body.classList.contains('calm') || matchMedia('(prefers-reduced-motion: reduce)').matches);
const dark = () => doc.documentElement.dataset.eff === 'dark';

/* ===================== 2. THREE.JS MODELS (pure: take THREE as a param) ===================== */
export function heartShape(THREE, s = 1) {
  const h = new THREE.Shape();
  h.moveTo(0, -0.5 * s);
  h.bezierCurveTo(-0.9 * s, 0.1 * s, -0.5 * s, 0.75 * s, 0, 0.3 * s);
  h.bezierCurveTo(0.5 * s, 0.75 * s, 0.9 * s, 0.1 * s, 0, -0.5 * s);
  return h;
}

function letterTexture(THREE) {
  const c = doc.createElement('canvas');
  c.width = 512; c.height = 560;
  const draw = () => {
    const g = c.getContext('2d');
    g.fillStyle = '#fffdf6'; g.fillRect(0, 0, 512, 560);
    g.strokeStyle = 'rgba(120,100,70,.18)'; g.lineWidth = 2;
    for (let y = 130; y < 540; y += 44) { g.beginPath(); g.moveTo(40, y); g.lineTo(472, y); g.stroke(); }
    g.fillStyle = '#2a4a8c'; g.font = '700 58px Caveat, cursive';
    g.fillText('Dear you,', 44, 92);
    g.fillStyle = 'rgba(42,74,140,.55)';
    [[44, 174, 330], [44, 218, 380], [44, 262, 290], [44, 306, 360]].forEach(([x, y, w]) => g.fillRect(x, y - 8, w, 4));
  };
  draw();
  const t = new THREE.CanvasTexture(c);
  t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = 4;
  if (doc.fonts && doc.fonts.load) doc.fonts.load('700 58px Caveat').then(() => { draw(); t.needsUpdate = true; }).catch(() => {});
  return t;
}

/* An envelope: body, V-pocket, hinged flap with wax seal, optional letter that slides out. */
export function buildEnvelope(THREE, o = {}) {
  const W = 2, H = 1.3, D = 0.05, hw = W / 2, hh = H / 2;
  const std = (color, extra = {}) => new THREE.MeshStandardMaterial({ color, roughness: 0.92, metalness: 0, ...extra });
  const g = new THREE.Group();
  const body = new THREE.Mesh(new THREE.BoxGeometry(W, H, D), std(o.paper ?? 0xfff6e4));
  g.add(body);
  const tri = (a, b, c, mat, z) => {
    const s = new THREE.Shape(); s.moveTo(...a); s.lineTo(...b); s.lineTo(...c); s.closePath();
    const m = new THREE.Mesh(new THREE.ShapeGeometry(s), mat); m.position.z = z; return m;
  };
  const pk = std(o.pocket ?? 0xf7e9c9, { side: THREE.DoubleSide });
  const pk2 = std(o.pocket2 ?? 0xefdcb6, { side: THREE.DoubleSide });
  const zp = D / 2 + 0.008;
  g.add(tri([-hw, hh], [-hw, -hh], [0, -0.02], pk, zp));
  g.add(tri([hw, hh], [0, -0.02], [hw, -hh], pk, zp));
  g.add(tri([-hw, -hh], [hw, -hh], [0, -0.02], pk2, zp));

  let letter = null;
  if (o.letter !== false) {
    letter = new THREE.Mesh(new THREE.PlaneGeometry(W * 0.88, 1.1), new THREE.MeshStandardMaterial({ map: letterTexture(THREE), roughness: 0.95 }));
    letter.position.set(0, -0.1, D / 2 + 0.003);
    g.add(letter);
  }

  const pivot = new THREE.Group();            // hinge on the top edge
  pivot.position.set(0, hh, D / 2 + 0.014);
  const apex = -H * 0.12;
  const fs = new THREE.Shape(); fs.moveTo(-hw, 0); fs.lineTo(hw, 0); fs.lineTo(0, apex - hh); fs.closePath();
  const flap = new THREE.Mesh(new THREE.ShapeGeometry(fs), std(o.flap ?? 0xf1dfba, { side: THREE.DoubleSide }));
  pivot.add(flap);
  const seal = new THREE.Group();
  const disc = new THREE.Mesh(new THREE.CylinderGeometry(0.15, 0.17, 0.045, 28), std(o.seal ?? 0xd9776d, { roughness: 0.5 }));
  disc.rotation.x = Math.PI / 2;
  const heart = new THREE.Mesh(new THREE.ExtrudeGeometry(heartShape(THREE, 0.15), { depth: 0.02, bevelEnabled: false }), std(0xe99a90, { roughness: 0.45 }));
  heart.position.z = 0.02;
  seal.add(disc, heart);
  seal.position.set(0, apex - hh + 0.02, 0.025);
  pivot.add(seal);
  g.add(pivot);

  const ease = (t) => t * t * (3 - 2 * t);
  /* open: 0 (closed) .. 1 (flap back, letter risen) */
  g.userData.setOpen = (t) => {
    pivot.rotation.x = Math.PI * 0.97 * ease(Math.min(1, t / 0.7));
    const back = ease(Math.max(0, (t - 0.25) / 0.6));
    pivot.position.z = (D / 2 + 0.014) - back * (D + 0.03);
    if (letter) letter.position.y = -0.1 + ease(Math.max(0, (t - 0.55) / 0.45)) * 0.62;
  };
  g.userData.setOpen(0);
  return g;
}

/* ===================== 3. SCENES ===================== */
let T;
const loadThree = () => (T ||= import(/* @vite-ignore */ THREE_URL));
let GLOK;
const hasGL = () => GLOK ??= (() => { try { const c = doc.createElement('canvas'); return !!(c.getContext('webgl2') || c.getContext('webgl')); } catch { return false; } })();

function makeRenderer(THREE, host) {
  const r = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'low-power' });
  r.setPixelRatio(Math.min(devicePixelRatio || 1, 2));
  r.toneMapping = THREE.ACESFilmicToneMapping; r.toneMappingExposure = 1.05;
  host.appendChild(r.domElement);
  r.domElement.addEventListener('webglcontextlost', (e) => e.preventDefault());
  return r;
}
function lights(THREE, scene) {
  const hemi = new THREE.HemisphereLight(0xffffff, 0xcdb89a, 1.9);
  const key = new THREE.DirectionalLight(0xfff1d6, 2.6); key.position.set(3, 5, 6);
  const rim = new THREE.DirectionalLight(0xffc9c0, 0.9); rim.position.set(-4, -2, -3);
  scene.add(hemi, key, rim);
  return () => { const d = dark(); hemi.intensity = d ? 0.95 : 1.9; key.intensity = d ? 1.6 : 2.6; };
}
/* run a draw fn only while the host is on screen and the tab is visible */
function runWhileVisible(host, frame, still) {
  let vis = true, raf = 0, t0 = performance.now(), prev = t0;
  const loop = (now) => { raf = 0; if (!vis || doc.hidden) return; frame((now - t0) / 1000, Math.min(0.05, (now - prev) / 1000)); prev = now; raf = requestAnimationFrame(loop); };
  const kick = () => { if (!raf && vis && !doc.hidden && !calm()) { prev = performance.now(); raf = requestAnimationFrame(loop); } };
  new IntersectionObserver((e) => { vis = e[0].isIntersecting; kick(); }).observe(host);
  doc.addEventListener('visibilitychange', kick);
  if (calm()) still(); else kick();
}

async function envelopeScene(host) {
  const THREE = await loadThree();
  const renderer = makeRenderer(THREE, host);
  const scene = new THREE.Scene();
  const cam = new THREE.PerspectiveCamera(28, 1, 0.1, 40);
  const setLights = lights(THREE, scene); setLights();
  new MutationObserver(() => setTimeout(setLights, 380)).observe(doc.documentElement, { attributes: true, attributeFilter: ['data-eff'] });
  const env = buildEnvelope(THREE, { paper: 0xfff6e4 });
  const holder = new THREE.Group(); holder.add(env); scene.add(holder);
  holder.rotation.z = -0.1;
  const size = () => {
    const w = host.clientWidth || 1, h = host.clientHeight || 1;
    renderer.setSize(w, h, false); cam.aspect = w / h; cam.updateProjectionMatrix();
    const visH = 3.05; cam.position.set(0, 0.42, visH / 2 / Math.tan(cam.fov * Math.PI / 360));
    holder.scale.setScalar(Math.min(1, (visH * cam.aspect) / 2.4));
  };
  size(); addEventListener('resize', size);

  const near = host.dataset.reach === 'near';
  const card = host.closest('.card') || host;
  let target = 0, open = 0, tx = 0, ty = 0, rx = 0, ry = 0;
  const track = (e) => {
    const r = host.getBoundingClientRect();
    tx = Math.max(-1, Math.min(1, ((e.clientX - r.left) / r.width) * 2 - 1));
    ty = Math.max(-1, Math.min(1, ((e.clientY - r.top) / r.height) * 2 - 1));
    if (near) target = Math.hypot(e.clientX - (r.left + r.width / 2), e.clientY - (r.top + r.height / 2)) < 240 ? 1 : 0;
  };
  if (near) addEventListener('pointermove', track, { passive: true });
  else {
    card.addEventListener('pointerenter', () => { target = 1; });
    card.addEventListener('pointerleave', () => { target = 0; tx = ty = 0; });
    card.addEventListener('pointermove', track, { passive: true });
    card.addEventListener('pointerdown', (e) => { if (e.pointerType !== 'mouse' && !e.target.closest('a,button')) target = target ? 0 : 1; });
  }

  const frame = (t, dt) => {
    open += (target - open) * Math.min(1, dt * 3); env.userData.setOpen(open);
    rx += (ty * 0.1 - rx) * Math.min(1, dt * 3); ry += (tx * 0.2 - ry) * Math.min(1, dt * 3);
    env.rotation.set(rx + Math.sin(t * 0.5) * 0.015, ry + Math.sin(t * 0.35) * 0.04, 0);
    env.position.y = Math.sin(t * 0.6) * 0.015 - open * 0.08;
    renderer.render(scene, cam);
  };
  runWhileVisible(host, frame, () => { env.userData.setOpen(0); env.rotation.set(0.05, -0.25, 0); renderer.render(scene, cam); });
}

/* ===================== 4. MOTION HELPERS ===================== */
function countUp() {
  if (calm()) return;
  doc.querySelectorAll('.stats big, .sc big, .streak').forEach((el) => {
    const m = el.textContent.match(/^(\s*)([\d,]+)(.*)$/s);
    if (!m) return;
    const end = parseInt(m[2].replace(/,/g, ''), 10);
    if (!end || end > 1e7) return;
    const t0 = performance.now() + 250, dur = 700;
    const step = (now) => {
      const p = Math.min(1, Math.max(0, (now - t0) / dur)), v = Math.round(end * (1 - Math.pow(1 - p, 3)));
      el.textContent = m[1] + v.toLocaleString('en-US') + m[3];
      if (p < 1) requestAnimationFrame(step);
    };
    el.textContent = m[1] + '0' + m[3];
    requestAnimationFrame(step);
  });
}

/* ===================== 5. ADMIN 3D CHART ===================== */
async function chart3d() {
  const card = doc.querySelector('.card.chart'), ch = doc.getElementById('ch'), AD = window.AD;
  if (!card || !ch || !AD || !AD.v || !AD.v.length || !hasGL()) return;
  const THREE = await loadThree();
  const V = AD.v, D = AD.d, n = V.length;
  const TOP = Math.ceil(Math.max(3, ...V) / 3) * 3, BW = 10.6, BH = 3.2, DEPTH = 0.9;
  const clamp = (x) => Math.max(0, Math.min(1, x)), outC = (x) => 1 - Math.pow(1 - x, 3);
  const fmt = (k) => new Date(k + 'T00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

  /* ui: host + 3D / 2D switch */
  const host = doc.createElement('div'); host.className = 'chw fx-c3d'; ch.after(host);
  const lg = card.querySelector('.lg');
  const seg = doc.createElement('div'); seg.className = 'fx-cseg'; seg.setAttribute('role', 'group'); seg.setAttribute('aria-label', 'Chart view');
  seg.innerHTML = '<button type="button" data-m="3d">3D</button><button type="button" data-m="2d">2D</button>';
  if (lg) { const w = doc.createElement('div'); w.className = 'fx-cr'; lg.replaceWith(w); w.append(seg, lg); } else card.prepend(seg);

  host.setAttribute('role', 'img'); host.setAttribute('aria-label', 'Daily active users, 3D bar chart. Switch to 2D for the line chart.');
  const renderer = makeRenderer(THREE, host);
  const scene = new THREE.Scene();
  const cam = new THREE.PerspectiveCamera(30, 1, 0.1, 60);
  const setLights = lights(THREE, scene);
  const G = new THREE.Group(); scene.add(G);

  const accent = () => { const i = doc.querySelector('.lg i'), c = i && getComputedStyle(i).backgroundColor; return new THREE.Color(c && c !== 'rgba(0, 0, 0, 0)' ? c : '#e9c84a'); };
  const acc = accent();
  const labels = [];
  const label = (text, anchor) => {
    const c = doc.createElement('canvas'); c.width = 192; c.height = 64;
    const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace;
    const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, depthWrite: false }));
    sp.scale.set(1.1, 0.367, 1);
    const paint = () => {
      const g = c.getContext('2d'); g.clearRect(0, 0, 192, 64);
      g.fillStyle = dark() ? '#b9b3a7' : '#7c7568'; g.font = '600 28px Manrope, sans-serif'; g.textBaseline = 'middle'; g.textAlign = anchor;
      g.fillText(text, anchor === 'right' ? 186 : 96, 32); tex.needsUpdate = true;
    };
    paint(); labels.push(paint);
    if (doc.fonts && doc.fonts.load) doc.fonts.load('600 28px Manrope').then(paint).catch(() => {});
    return sp;
  };

  /* floor, back wall, grid lines, labels */
  const floor = new THREE.Mesh(new THREE.BoxGeometry(BW + 0.6, 0.08, 2.3), new THREE.MeshStandardMaterial({ roughness: 0.9 }));
  floor.position.set(0, -0.04, 0.1); G.add(floor);
  const wall = new THREE.Mesh(new THREE.PlaneGeometry(BW + 0.6, BH + 0.2), new THREE.MeshBasicMaterial({ transparent: true, opacity: 0.5, depthWrite: false }));
  wall.position.set(0, BH / 2 + 0.1, -1.0); G.add(wall);
  const gridMats = [];
  for (let k = 0; k <= 3; k++) {
    const y = (k / 3) * BH, g = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(-BW / 2 - 0.2, y, -0.98), new THREE.Vector3(BW / 2 + 0.2, y, -0.98)]);
    const m = new THREE.LineBasicMaterial({ transparent: true, opacity: 0.9 }); gridMats.push(m); G.add(new THREE.Line(g, m));
    const t = label(String((TOP / 3) * k), 'right'); t.position.set(-BW / 2 - 0.35 - 0.55, y, -0.98); G.add(t);
  }
  D.forEach((k, i) => { if (i % 5 === 0 || i === n - 1) { const t = label(fmt(k), 'center'); t.position.set(-BW / 2 + (BW / n) * (i + 0.5), -0.28, 1.3); G.add(t); } });

  /* bars */
  const geo = new THREE.BoxGeometry(1, 1, 1); geo.translate(0, 0.5, 0);
  const step = BW / n, bw = Math.min(0.9, step * 0.62);
  const bars = V.map((v, i) => {
    const m = new THREE.Mesh(geo, new THREE.MeshStandardMaterial({ color: acc, roughness: 0.5, metalness: 0.05 }));
    m.position.set(-BW / 2 + step * (i + 0.5), 0, 0); m.scale.set(bw, 0.001, DEPTH);
    m.userData = { i, h: Math.max(0.05, (v / TOP) * BH), hl: 0 }; G.add(m); return m;
  });

  const applyTheme = () => {
    const d = dark(), a = accent();
    floor.material.color.set(d ? 0x2b2832 : 0xf1e9d9); wall.material.color.set(d ? 0x24212b : 0xf6f0e3);
    gridMats.forEach((m) => m.color.set(d ? 0x474152 : 0xd9cfbb));
    bars.forEach((b) => b.material.color.copy(a)); acc.copy(a);
    labels.forEach((p) => p()); setLights();
  };
  applyTheme();
  new MutationObserver(() => setTimeout(applyTheme, 420)).observe(doc.documentElement, { attributes: true, attributeFilter: ['data-eff'] });

  const size = () => {
    const w = host.clientWidth, h = host.clientHeight; if (!w || !h) return;
    renderer.setSize(w, h, false); cam.aspect = w / h; cam.updateProjectionMatrix();
    const t = Math.tan(cam.fov * Math.PI / 360), dist = Math.max(13.2 / (2 * t * cam.aspect), 5.8 / (2 * t)), pa = 0.38;
    cam.position.set(0, 1.5 + dist * Math.sin(pa), dist * Math.cos(pa)); cam.lookAt(0, 1.45, 0);
  };
  new ResizeObserver(size).observe(host);

  /* hover: gentle parallax + bar pick + tooltip */
  const tip = doc.createElement('div'); tip.className = 'tip'; tip.style.display = 'none'; host.appendChild(tip);
  const ray = new THREE.Raycaster(), v2 = new THREE.Vector2();
  let tx = 0, ty = 0, yaw = 0, pitch = 0, hover = -1, t0 = performance.now(), mode = 'x';
  host.addEventListener('pointermove', (e) => {
    const r = host.getBoundingClientRect();
    tx = ((e.clientX - r.left) / r.width) * 2 - 1; ty = ((e.clientY - r.top) / r.height) * 2 - 1;
    v2.set(tx, -ty); ray.setFromCamera(v2, cam);
    let i = -1; const hit = ray.intersectObjects(bars)[0];
    if (hit) i = hit.object.userData.i;
    else { const w = ray.intersectObject(wall)[0]; if (w) { const p = G.worldToLocal(w.point.clone()); const k = Math.round((p.x + BW / 2) / step - 0.5); if (k >= 0 && k < n) i = k; } }
    hover = i;
    if (i < 0) { tip.style.display = 'none'; return; }
    tip.innerHTML = '<small>' + fmt(D[i]) + '</small><b>' + V[i] + ' active user' + (V[i] === 1 ? '' : 's') + '</b>';
    tip.style.display = ''; tip.style.left = Math.min(e.clientX - r.left + 14, r.width - 150) + 'px'; tip.style.top = Math.max(0, e.clientY - r.top - 54) + 'px';
  });
  host.addEventListener('pointerleave', () => { tx = ty = 0; hover = -1; tip.style.display = 'none'; });

  const draw = (since, dt) => {
    const k = Math.min(1, dt * 3);
    yaw += (tx * 0.3 - yaw) * k; pitch += (ty * 0.07 - pitch) * k;
    G.rotation.set(pitch, -0.16 + yaw, 0);
    bars.forEach((b, i) => {
      const u = b.userData; u.hl += ((i === hover ? 1 : 0) - u.hl) * Math.min(1, dt * 8);
      b.scale.y = Math.max(0.001, u.h * outC(clamp((since - i * 0.022) / 0.8)));
      b.material.emissive.copy(acc).multiplyScalar(u.hl * 0.45);
    });
    renderer.render(scene, cam);
  };
  runWhileVisible(host, (t, dt) => draw((performance.now() - t0) / 1000, dt), () => draw(99, 0.016));

  const setMode = (m) => {
    if (m === mode) return; mode = m;
    try { localStorage.setItem('unsent-chart', m); } catch (e) {}
    ch.hidden = m === '3d'; host.hidden = m !== '3d';
    seg.querySelectorAll('button').forEach((b) => b.classList.toggle('on', b.dataset.m === m));
    if (m === '3d') { size(); t0 = performance.now(); if (calm()) draw(99, 0.016); } else dispatchEvent(new Event('resize'));
  };
  seg.addEventListener('click', (e) => { const b = e.target.closest('button[data-m]'); if (b) setMode(b.dataset.m); });
  let saved = '3d'; try { saved = localStorage.getItem('unsent-chart') || '3d'; } catch (e) {}
  host.hidden = true; setMode(saved === '2d' ? '2d' : '3d');
  if (saved === '2d') { seg.querySelector('[data-m="2d"]').classList.add('on'); }
}

/* ===================== 6. MOTION HELPERS: icon draw + scroll reveal ===================== */
function drawIcons() {
  if (calm()) return;
  doc.querySelectorAll('.ic svg, .ico svg, .sc > .between svg').forEach((svg) => {
    svg.querySelectorAll('path,circle,rect,line,polyline').forEach((el, i) => {
      el.setAttribute('pathLength', '1'); el.classList.add('fx-d'); el.style.setProperty('--di', i);
    });
  });
}
function scrollReveal() {
  if (calm() || !('IntersectionObserver' in window)) return;
  const kinds = [['.hside > *', 'right'], ['.card.chart', 'wipe'], ['.sg > .sc', 'grow'], ['.at tbody tr', 'fade'], ['.row', 'left'], ['.card, .green, .afoot', 'rise']];
  const seen = new Set(), todo = [];
  kinds.forEach(([sel, kind]) => doc.querySelectorAll(sel).forEach((el) => {
    if (seen.has(el)) return; seen.add(el);
    const r = el.getBoundingClientRect();
    if (r.height && r.top > innerHeight * 0.96) { el.setAttribute('data-rv', kind); todo.push(el); }
  }));
  if (!todo.length) return;
  const io = new IntersectionObserver((ents) => {
    ents.filter((e) => e.isIntersecting).forEach((e, i) => {
      const el = e.target; io.unobserve(el);
      el.style.transitionDelay = i * 60 + 'ms'; el.classList.add('rv-in');
      setTimeout(() => { el.removeAttribute('data-rv'); el.classList.remove('rv-in'); el.style.transitionDelay = ''; }, 1200 + i * 60);
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
  todo.forEach((el) => io.observe(el));
}

/* ===================== POSTCARD CAPTION: colour follows the picture =====================
   Samples the strip of the front image where the caption sits, then sets --cap-bg / --cap-ink /
   --cap-edge on the card. Light picture -> pale tinted label + deep ink. Dark picture -> deep
   tinted label + pale ink. Works for the built-in cards and for the person's own upload. */
function capTint(img) {
  try {
    const card = img.closest('.pc'); if (!card || !img.naturalWidth) return;
    const W = img.naturalWidth, H = img.naturalHeight;
    const c = doc.createElement('canvas'); c.width = 32; c.height = 8;
    const g = c.getContext('2d', { willReadFrequently: true });
    g.drawImage(img, W * 0.04, H * 0.78, W * 0.84, H * 0.16, 0, 0, 32, 8);
    const d = g.getImageData(0, 0, 32, 8).data;
    /* average lightness decides light or dark label; the strongest colours decide the hue */
    let lum = 0, vx = 0, vy = 0, sw = 0, ss = 0, n = 0;
    for (let i = 0; i < d.length; i += 4) {
      const r = d[i] / 255, gr = d[i + 1] / 255, b = d[i + 2] / 255;
      const mx = Math.max(r, gr, b), mn = Math.min(r, gr, b), dl = mx - mn, l = (mx + mn) / 2;
      lum += l; n++;
      if (!dl) continue;
      const s = dl / (1 - Math.abs(2 * l - 1) || 1);
      let h = mx === r ? ((gr - b) / dl) % 6 : mx === gr ? (b - r) / dl + 2 : (r - gr) / dl + 4;
      h *= Math.PI / 3;
      const w = s * s;                       /* vivid pixels count much more than grey ones */
      vx += Math.cos(h) * w; vy += Math.sin(h) * w; sw += w; ss += s * w;
    }
    lum /= n;
    let hue = sw ? Math.atan2(vy, vx) * 180 / Math.PI : 0; if (hue < 0) hue += 360;
    let sat = sw ? Math.min(1, ss / sw) : 0;
    if (hue < 55 || hue > 335) hue = (hue + 360 - 10) % 360;      /* warm picture: lean a little toward red */
    const green = hue > 75 && hue < 170;
    sat = Math.max(green ? 0.4 : 0.38, Math.min(0.7, sat * 1.25));  /* never fall back to grey */
    const S = (k) => Math.round(sat * k);
    let bg, ink, edge;
    if (lum > 0.5) {
      bg = `hsla(${hue}, ${S(75)}%, 93%, .94)`; ink = `hsl(${hue}, ${S(85)}%, 16%)`; edge = `hsla(${hue}, ${S(70)}%, 28%, .22)`;
    } else {
      bg = `hsla(${hue}, ${S(green ? 85 : 75)}%, ${green ? 11 : 15}%, .92)`;
      ink = `hsl(${hue}, ${S(45)}%, 93%)`; edge = `hsla(${hue}, ${S(60)}%, 62%, .28)`;
    }
    card.style.setProperty('--cap-bg', bg); card.style.setProperty('--cap-ink', ink); card.style.setProperty('--cap-edge', edge);
  } catch (e) { /* tainted canvas or no pixels: keep the default white label */ }
}
if (doc) {
  doc.addEventListener('load', (e) => { const t = e.target; if (t && t.tagName === 'IMG' && t.closest && t.closest('.pc-front')) capTint(t); }, true);
  const scan = () => doc.querySelectorAll('.pc-front img').forEach((i) => i.complete && capTint(i));
  doc.readyState === 'loading' ? doc.addEventListener('DOMContentLoaded', scan) : scan();
}

/* ===================== BOOT ===================== */
function start() {
  countUp(); drawIcons(); scrollReveal();
  chart3d().catch((e) => console.warn('[fx3d] chart skipped:', e && e.message));
  const hosts = [...doc.querySelectorAll('[data-scene]')];
  if (!hosts.length || !hasGL()) return;
  hosts.forEach((h) => {
    const fn = { envelope: envelopeScene }[h.dataset.scene];
    if (fn) fn(h).catch((e) => console.warn('[fx3d] scene skipped:', e && e.message));
  });
}
if (doc) (doc.readyState === 'loading' ? doc.addEventListener('DOMContentLoaded', start) : start());
