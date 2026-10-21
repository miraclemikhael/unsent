/* Unsent · js/letters.js · My letters: search, filter, open. Loaded in order by includes/footer.php (core.js first). */
/* global $, CSRF, post, Swal */
if (window.q) {
  const R = $(".row");
  let f = "all";
  const none = document.createElement("p");
  none.className = "mut nores";
  none.textContent = "No letters match that search.";
  none.hidden = true;
  if (R.length) R[R.length - 1].after(none);
  const flt = () => {
    let shown = 0;
    R.forEach((r) => {
      const hit =
        (f == "all" || r.dataset.k == f) &&
        (r.dataset.t + " " + r.dataset.b).toLowerCase().includes(q.value.trim().toLowerCase());
      r.style.display = hit ? "" : "none";
      if (hit) shown++;
    });
    none.hidden = shown > 0;
  };
  q.oninput = flt;
  $("[data-f]").forEach(
    (c) =>
      (c.onclick = () => {
        $("[data-f]").forEach((x) => x.classList.remove("on"));
        c.classList.add("on");
        f = c.dataset.f;
        flt();
      }),
  );
  R.forEach(
    (r) =>
      (r.onclick = () => {
        R.forEach((x) => x.classList.remove("on"));
        r.classList.add("on");
        st.textContent = r.dataset.t;
        sd.textContent = r.dataset.d;
        sb.textContent = r.dataset.b;
        stag.textContent = r.dataset.tag;
        stag.className = "tag " + r.dataset.k;
        se.href = "?p=editor&id=" + r.dataset.id;
        sid.value = r.dataset.id;
        spic.src = r.dataset.pc || "img/bottle.png";
        spic.classList.toggle("pcpic", !!r.dataset.pc);
        const sp = document.querySelector(".side");
        if (sp) {
          sp.classList.remove("swap");
          void sp.offsetWidth;
          sp.classList.add("swap");
        }
      }),
  );
  R.forEach(
    (r) =>
      (r.onkeydown = (e) => {
        if (e.key == "Enter" || e.key == " ") {
          e.preventDefault();
          r.click();
        }
      }),
  );
  const want = new URLSearchParams(location.search).get("open");
  const pick = R.find((r) => r.dataset.id == want) || R[0];
  if (pick) {
    pick.click();
    want && pick.scrollIntoView({ block: "center" });
  }
}

