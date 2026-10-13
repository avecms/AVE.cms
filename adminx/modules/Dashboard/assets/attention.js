(function (window, document) {
  'use strict';
  var A = window.Adminx || (window.Adminx = {});
  var root = document.querySelector('[data-attention-root]');
  if (!root) return;
  var endpoint = root.dataset.attentionUrl;
  var storageKey = 'ave.attention.' + endpoint + '.' + root.dataset.attentionUser;
  var timer = null, busy = false, stopped = false, lastRequest = 0, seen = {};
  try { seen = JSON.parse(sessionStorage.getItem(storageKey) || '{}') || {}; } catch (e) {}

  function saveSeen() { try { sessionStorage.setItem(storageKey, JSON.stringify(seen)); } catch (e) {} }
  function update(payload) {
    var present = {};
    payload.items.forEach(function (item) {
      present[item.key] = true;
      var baseline = seen[item.key];
      var arriving = item.available && baseline && (item.latest_id > baseline.latest_id || item.count > baseline.count);
      if (item.available) {
        seen[item.key] = { latest_id: Math.max(item.latest_id, baseline ? baseline.latest_id : 0), count: item.count };
      }
      document.querySelectorAll('[data-attention-key]').forEach(function (el) {
        if (el.dataset.attentionKey !== item.key) return;
        el.hidden = false;
        el.href = item.url;
        el.classList.toggle('has-pending', item.available && item.count > 0);
        el.classList.toggle('is-stale', !item.available);
        var count = el.querySelector('[data-attention-count]');
        if (count) count.textContent = item.available ? (el.classList.contains('attention-shortcut') && item.count > 99 ? '99+' : item.count) : '\u2014';
        var wait = el.querySelector('[data-attention-wait]');
        if (wait) wait.textContent = item.wait;
        if (el.classList.contains('attention-shortcut')) {
          var label = item.title + ': ' + (item.available ? item.count : '\u2014') + ' \u00b7 ' + item.wait;
          el.setAttribute('aria-label', label);
          el.setAttribute('data-tooltip', label);
        }
        if (arriving) {
          el.classList.remove('is-arriving');
          void el.offsetWidth;
          el.classList.add('is-arriving');
          window.setTimeout(function () { el.classList.remove('is-arriving'); }, 1500);
        }
      });
    });
    document.querySelectorAll('[data-attention-key]').forEach(function (el) {
      if (!present[el.dataset.attentionKey]) el.hidden = true;
    });
    var any = Object.keys(present).length > 0;
    root.hidden = !any;
    var overview = document.querySelector('[data-attention-overview]');
    if (overview) overview.hidden = !any;
    stale(false);
    saveSeen();
  }
  function stale(value) {
    root.classList.toggle('is-stale', value);
    document.querySelectorAll('[data-attention-health]').forEach(function (el) { el.hidden = !value; });
    if (value) root.querySelectorAll('.attention-shortcut').forEach(function (el) { el.classList.add('is-stale'); });
  }
  function schedule(delay) {
    window.clearTimeout(timer);
    if (!stopped && !document.hidden) timer = window.setTimeout(refresh, delay || 30000);
  }
  function refresh() {
    if (stopped || busy || document.hidden) return;
    if (Date.now() - lastRequest < 3000) { schedule(3000); return; }
    busy = true; lastRequest = Date.now();
    var controller = new AbortController();
    var timeout = window.setTimeout(function () { controller.abort(); }, 10000);
    fetch(endpoint, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(function (response) {
        if (response.status === 401 || response.status === 403 || response.status === 428 || response.redirected) {
          stopped = true;
          throw Error('Session unavailable');
        }
        if (!response.ok) throw Error('Refresh failed');
        return response.json();
      }).then(function (result) {
        if (!result.success || !result.data || !Array.isArray(result.data.items)) throw Error('Invalid summary');
        update(result.data);
      }).catch(function () { stale(true); })
      .finally(function () { window.clearTimeout(timeout); busy = false; schedule(); });
  }
  try { update(JSON.parse(root.dataset.attentionInitial)); } catch (e) { stale(true); }
  document.addEventListener('visibilitychange', function () { window.clearTimeout(timer); if (!document.hidden) refresh(); });
  document.addEventListener('adminx:drawer:closed', function () { schedule(1000); });
  document.addEventListener('adminx:form-saved', function () { schedule(1000); });
  window.addEventListener('pageshow', function (event) { if (event.persisted) refresh(); });
  A.Attention = { refresh: refresh };
  schedule();
})(window, document);
