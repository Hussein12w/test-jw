/* JW Store theme — menus, drawers, side cart, wishlist, slider, quantity buttons. */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var C = window.JW || {};

  function toast(msg) {
    var box = $('#jwToasts'); if (!box || !msg) return;
    var el = document.createElement('div'); el.className = 'jw-toast'; el.textContent = msg; box.appendChild(el);
    requestAnimationFrame(function () { el.classList.add('show'); });
    setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 300); }, 2600);
  }

  /* Drawers: menu, cart, shop filters */
  function drawer(id, open) {
    var d = document.getElementById(id); if (!d) return;
    var show = open === undefined ? d.hidden : open;
    d.hidden = !show; document.documentElement.classList.toggle('jw-lock', show);
    if (show) requestAnimationFrame(function () { d.classList.add('open'); }); else d.classList.remove('open');
  }
  function filters() { var f = $('#jwFilters'); if (f) { f.classList.toggle('open'); document.documentElement.classList.toggle('jw-lock', f.classList.contains('open')); } }

  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-jw]');
    if (t) {
      var a = t.dataset.jw;
      if (a === 'menu') { e.preventDefault(); drawer('jwMenu'); }
      else if (a === 'cart' && $('#jwCart')) { e.preventDefault(); drawer('jwCart'); }
      else if (a === 'filters') { e.preventDefault(); filters(); }
      else if (a === 'search') { e.preventDefault(); document.body.classList.toggle('jw-search-open'); var i = $('.jw-header .jw-search input[type=search]'); if (i && document.body.classList.contains('jw-search-open')) i.focus(); }
      return;
    }
    /* Dropdowns (language, all categories) */
    var dd = e.target.closest('[data-jw-dd] > button');
    $$('[data-jw-dd].open').forEach(function (x) { if (!dd || x !== dd.parentNode) { x.classList.remove('open'); var b = x.querySelector('button'); if (b) b.setAttribute('aria-expanded', 'false'); } });
    if (dd) { var p = dd.parentNode, o = !p.classList.contains('open'); p.classList.toggle('open', o); dd.setAttribute('aria-expanded', o ? 'true' : 'false'); return; }
    /* Wishlist hearts */
    var w = e.target.closest('[data-wish]');
    if (w) { e.preventDefault(); toggleWish(w.dataset.wish); return; }
    /* Hero dots */
    var dot = e.target.closest('.jw-dots button');
    if (dot) { slide(+dot.dataset.i); restart(); }
    /* Quantity buttons */
    var q = e.target.closest('.jw-qty-btn');
    if (q) {
      var inp = q.parentNode.querySelector('input.qty'); if (!inp) return;
      var step = parseFloat(inp.step) || 1, min = inp.min !== '' ? parseFloat(inp.min) : 0, max = inp.max !== '' ? parseFloat(inp.max) : Infinity;
      var v = (parseFloat(inp.value) || 0) + (q.dataset.d === '+' ? step : -step);
      inp.value = Math.max(min, Math.min(max, v));
      inp.dispatchEvent(new Event('change', { bubbles: true }));
      var upd = $('button[name=update_cart]'); if (upd) { upd.disabled = false; upd.removeAttribute('aria-disabled'); }
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { drawer('jwMenu', false); drawer('jwCart', false); var f = $('#jwFilters'); if (f) f.classList.remove('open'); document.documentElement.classList.remove('jw-lock'); $$('[data-jw-dd].open').forEach(function (x) { x.classList.remove('open'); }); }
  });

  /* Wishlist in a cookie */
  function wish() { var m = document.cookie.match(/(?:^|; )jw_wish=([^;]*)/); return m ? decodeURIComponent(m[1]).split(',').filter(Boolean) : []; }
  function paintWish() {
    var w = wish();
    $$('[data-wish]').forEach(function (b) { b.classList.toggle('on', w.indexOf(b.dataset.wish) > -1); });
    $$('.jw-wish-count').forEach(function (c) { c.textContent = w.length; c.classList.toggle('is-empty', !w.length); });
  }
  function toggleWish(id) {
    var w = wish(), i = w.indexOf(id), on = i < 0;
    if (on) w.unshift(id); else w.splice(i, 1);
    document.cookie = 'jw_wish=' + encodeURIComponent(w.slice(0, 100).join(',')) + ';path=/;max-age=31536000;samesite=lax';
    paintWish(); toast(on ? C.wishAdded : C.wishRm);
  }
  paintWish();

  /* Hero slider */
  var slides = $$('.jw-slide'), cur = 0, timer = null;
  function slide(i) { if (!slides.length) return; cur = (i + slides.length) % slides.length; slides.forEach(function (s, k) { s.classList.toggle('on', k === cur); }); $$('.jw-dots button').forEach(function (d, k) { d.classList.toggle('on', k === cur); }); }
  function restart() { clearInterval(timer); if (slides.length > 1) timer = setInterval(function () { slide(cur + 1); }, 6000); }
  restart();
  /* Swipe on the hero */
  var hero = $('.jw-hero'), sx = null;
  if (hero) {
    hero.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) { if (sx === null) return; var dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 40) { slide(cur + ((dx < 0) !== !!C.rtl ? 1 : -1)); restart(); } sx = null; });
  }

  /* Quantity − / + around WooCommerce quantity inputs */
  function qtyButtons(root) {
    $$('.quantity', root).forEach(function (q) {
      if (q.dataset.jwq || !q.querySelector('input.qty') || q.querySelector('input.qty').type === 'hidden') return;
      q.dataset.jwq = 1;
      var m = document.createElement('button'); m.type = 'button'; m.className = 'jw-qty-btn'; m.dataset.d = '-'; m.textContent = '−'; m.setAttribute('aria-label', '-');
      var p = document.createElement('button'); p.type = 'button'; p.className = 'jw-qty-btn'; p.dataset.d = '+'; p.textContent = '+'; p.setAttribute('aria-label', '+');
      q.insertBefore(m, q.firstChild); q.appendChild(p);
    });
  }
  qtyButtons();

  /* Header shadow when scrolling */
  var hdr = $('#jwHeader');
  window.addEventListener('scroll', function () { if (hdr) hdr.classList.toggle('scrolled', window.scrollY > 10); }, { passive: true });

  /* Side cart after adding a product.
     1) Product page: the form is sent in the background, then the side cart opens (no page reload).
        "Buy now" and forms that return an error are submitted normally.
     2) Any other add-to-cart that reloads the page (e.g. AJAX buttons turned off): the server sets
        the jw_open_cart cookie and the side cart opens on the next page. */
  function getCookie(n) { var m = document.cookie.match(new RegExp('(?:^|; )' + n + '=([^;]*)')); return m ? m[1] : null; }
  function dropCookie(n) { document.cookie = n + '=;path=/;max-age=0;samesite=lax'; }
  function openSideCart() {
    if (!C.sideCart || !$('#jwCart') || document.body.classList.contains('woocommerce-cart') || document.body.classList.contains('woocommerce-checkout')) return;
    drawer('jwCart', true);
  }
  if (getCookie('jw_open_cart')) { dropCookie('jw_open_cart'); openSideCart(); }
  /* Remove ?add-to-cart= from the address so refreshing the page does not add the product again */
  if (/[?&]add-to-cart=/.test(location.search) && window.history && history.replaceState) {
    var q = location.search.replace(/([?&])(add-to-cart|quantity)=[^&]*&?/g, '$1').replace(/[?&]$/, '');
    history.replaceState(null, '', location.pathname + q + location.hash);
  }
  function refreshThenOpen() {
    var done = false, go = function () { if (!done) { done = true; openSideCart(); } };
    if (window.jQuery) { window.jQuery(document.body).one('wc_fragments_refreshed wc_fragments_loaded', go).trigger('wc_fragment_refresh'); }
    setTimeout(go, 2500);
  }
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!C.sideCart || !f.matches || !f.matches('form.cart') || f.dataset.jwPlain || !window.fetch || !window.FormData) return;
    var sub = e.submitter;
    if (sub && sub.name === 'jw_buy_now') return;
    if (f.closest('.product-type-external') || f.classList.contains('grouped_form')) return;
    e.preventDefault();
    var fd = new FormData(f);
    if (sub && sub.name && !fd.has(sub.name)) fd.append(sub.name, sub.value);
    var btn = sub || f.querySelector('.single_add_to_cart_button');
    if (btn) btn.classList.add('loading');
    var plain = function () { f.dataset.jwPlain = 1; if (sub && sub.click) sub.click(); else f.submit(); };
    fetch(f.getAttribute('action') || location.href, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        if (btn) btn.classList.remove('loading');
        if (/class="[^"]*(woocommerce-error|is-error)/.test(html)) { plain(); return; }
        dropCookie('jw_open_cart');
        refreshThenOpen();
      })
      .catch(function () { if (btn) btn.classList.remove('loading'); plain(); });
  });

  /* WooCommerce events (jQuery): open the side cart after AJAX add-to-cart; re-add quantity buttons after cart updates */
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', function () { dropCookie('jw_open_cart'); openSideCart(); });
    window.jQuery(document.body).on('updated_cart_totals updated_wc_div wc_fragments_refreshed updated_checkout', function () { qtyButtons(); });
  }
})();
