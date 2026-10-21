/* Unsent · js/sidebar.js · sidebar menu, profile photo, saved toast. Loaded in order by includes/footer.php (core.js first). */
/* global $, CSRF, post, Swal */
/* sidebar user menu, profile photo, saved toast */
(() => {
  const S = window.Swal,
    toast = (t, i) =>
      S &&
      S.fire({
        toast: true,
        position: "top-end",
        icon: i || "success",
        title: t,
        showConfirmButton: false,
        timer: 2400,
        timerProgressBar: true,
      });
  if (window.FLASH) toast(window.FLASH, window.FLASH_T);
  const b = document.getElementById("meb"),
    m = document.getElementById("mem");
  if (b && m) {
    const shut = () => {
      m.hidden = true;
      b.setAttribute("aria-expanded", "false");
    };
    b.onclick = (e) => {
      e.stopPropagation();
      const open = m.hidden;
      m.hidden = !open;
      b.setAttribute("aria-expanded", String(open));
      if (open) m.querySelector("a").focus();
    };
    document.addEventListener("click", (e) => {
      if (!m.contains(e.target)) shut();
    });
    addEventListener("keydown", (e) => {
      if (e.key == "Escape" && !m.hidden) {
        shut();
        b.focus();
      }
    });
    m.querySelectorAll("a").forEach((a) => a.addEventListener("click", shut));
  }
  const ab = document.getElementById("avb");
  if (!ab) return;
  const f = document.getElementById("avf"),
    x = document.getElementById("avx"),
    msg = document.getElementById("avm"),
    nm = document.querySelector("input[name=name]");
  const show = (src) => {
    $(".av").forEach((a) => {
      if (src) {
        a.style.backgroundImage = "url('" + src + "?" + Date.now() + "')";
        a.classList.add("has");
        a.textContent = "";
      } else {
        a.style.backgroundImage = "";
        a.classList.remove("has");
        a.textContent = (nm.value.trim().charAt(0) || "?").toUpperCase();
      }
    });
    x.hidden = !src;
  };
  const was = msg.textContent;
  ab.onclick = () => f.click();
  f.onchange = async () => {
    const file = f.files[0];
    if (!file) return;
    msg.textContent = "Uploading…";
    const fd = new FormData();
    fd.append("a", "avup");
    fd.append("pic", file);
    try {
      const r = await (await post(fd)).json();
      if (r.ok) {
        show(r.src);
        msg.textContent = was;
        toast("Photo updated");
      } else msg.textContent = r.err;
    } catch (e) {
      msg.textContent = "Upload failed, try again.";
    }
    f.value = "";
  };
  x.onclick = async () => {
    const fd = new FormData();
    fd.append("a", "avrm");
    try {
      const r = await (await post(fd)).json();
      if (r.ok) {
        show("");
        toast("Photo removed");
      }
    } catch (e) {
      msg.textContent = "Could not remove it, try again.";
    }
  };
})();

