{{--
    NATIVE_EVENT_TRACKER (2026-05-30)
    --------------------------------------------------------------
    Site-wide interaction capture. Posts batched events to /api/events.
    No external trackers. Self-hosted. Privacy-first.

    Captures:
      • click      — every click anywhere (with CSS path + viewport coords)
      • scroll     — max scroll % reached per page (debounced, sent on unload)
      • hover      — sampled mouseover dwell (1 in 20) for hotspot density
      • time_on_page — milliseconds active (excludes background tabs)
      • named events — qa_open, audio_play_30s, pdf_download, etc., via
                       window.shTrack(name, meta)

    Respects DNT (Do-Not-Track). Skips for authenticated users.
    Batched every 5s OR on page unload (whichever first).
--}}
@php
    // Don't track admins/clerks — their clicks pollute visitor heatmaps
    $shouldTrack = ! auth()->check();
@endphp
@if($shouldTrack)
<script>
(function () {
  'use strict';
  if (navigator.doNotTrack === '1' || window.doNotTrack === '1') return;

  // ── Session ID — same v_sid cookie as page_views middleware
  function getSid() {
    var m = document.cookie.match(/(?:^|;\s*)v_sid=([^;]+)/);
    return m ? m[1] : null;
  }
  var SID = getSid();
  if (!SID) {
    // Server hasn't set the cookie yet for some reason — generate a temporary
    // one so this pageload still tracks. Real cookie arrives next pageview.
    SID = ('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx').replace(/x/g, function() {
      return ((Math.random() * 16) | 0).toString(16);
    });
  }

  // ── Batched event queue
  var queue = [];
  var FLUSH_MS = 5000;
  var MAX_QUEUE = 40;
  var flushTimer = null;

  function send(opts) {
    if (queue.length === 0) return;
    var batch = queue.slice();
    queue.length = 0;
    var body = JSON.stringify({ events: batch, session_id: SID });
    var url = '/api/events';
    // sendBeacon survives page-unload; fetch is the fallback
    if (opts && opts.beacon && navigator.sendBeacon) {
      try {
        navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }));
        return;
      } catch (e) {}
    }
    try {
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: body,
        keepalive: true,
      }).catch(function(){});
    } catch (e) {}
  }

  function scheduleFlush() {
    if (flushTimer) return;
    flushTimer = setTimeout(function () {
      flushTimer = null;
      send();
    }, FLUSH_MS);
  }

  function enqueue(ev) {
    ev.ts = Date.now();
    ev.path = location.pathname;
    queue.push(ev);
    if (queue.length >= MAX_QUEUE) {
      if (flushTimer) { clearTimeout(flushTimer); flushTimer = null; }
      send();
    } else {
      scheduleFlush();
    }
  }

  // ── CSS-path generator — stable enough for heatmap aggregation
  function cssPath(el) {
    if (!el || el.nodeType !== 1) return '';
    if (el.id) return '#' + el.id;
    var parts = [];
    var depth = 0;
    while (el && el.nodeType === 1 && depth < 5) {
      var seg = el.tagName.toLowerCase();
      if (el.className && typeof el.className === 'string') {
        var cls = el.className.trim().split(/\s+/).slice(0, 2).join('.');
        if (cls) seg += '.' + cls;
      }
      var sib = el.previousElementSibling;
      var idx = 1;
      while (sib) { idx++; sib = sib.previousElementSibling; }
      seg += ':nth-child(' + idx + ')';
      parts.unshift(seg);
      el = el.parentElement;
      depth++;
    }
    return parts.join(' > ').slice(0, 255);
  }

  function elText(el) {
    if (!el) return '';
    var txt = (el.innerText || el.textContent || '').trim().replace(/\s+/g, ' ');
    return txt.slice(0, 80);
  }

  // ── Viewport tracking
  function vp() { return { vw: window.innerWidth, vh: window.innerHeight }; }

  // ── CLICKS — capture every click, anywhere
  document.addEventListener('click', function (e) {
    var el = e.target;
    if (!el || el.nodeType !== 1) return;
    var v = vp();
    enqueue({
      type: 'click',
      sel: cssPath(el),
      txt: elText(el),
      x: Math.round(e.clientX),
      y: Math.round(e.clientY),
      vw: v.vw, vh: v.vh,
    });
  }, { passive: true, capture: true });

  // ── HOVERS — sampled mouseovers (1 in 20) for hotspot density
  var hoverCounter = 0;
  document.addEventListener('mouseover', function (e) {
    hoverCounter++;
    if (hoverCounter % 20 !== 0) return;
    var el = e.target;
    if (!el || el.nodeType !== 1) return;
    var v = vp();
    enqueue({
      type: 'hover',
      sel: cssPath(el),
      x: Math.round(e.clientX),
      y: Math.round(e.clientY),
      vw: v.vw, vh: v.vh,
    });
  }, { passive: true, capture: true });

  // ── SCROLL — track max % reached per page, send on unload
  var maxScrollPct = 0;
  var scrollTimer = null;
  function updateScroll() {
    var docH = Math.max(
      document.documentElement.scrollHeight,
      document.body.scrollHeight
    );
    var winH = window.innerHeight;
    var top  = window.pageYOffset || document.documentElement.scrollTop;
    if (docH <= winH) return; // page fits in viewport
    var pct = Math.min(100, Math.round(((top + winH) / docH) * 100));
    if (pct > maxScrollPct) maxScrollPct = pct;
  }
  window.addEventListener('scroll', function () {
    if (scrollTimer) return;
    scrollTimer = setTimeout(function () { scrollTimer = null; updateScroll(); }, 200);
  }, { passive: true });

  // ── TIME ON PAGE — count only when tab is visible
  var startMs = Date.now();
  var activeMs = 0;
  var lastVisible = Date.now();
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      activeMs += Date.now() - lastVisible;
    } else {
      lastVisible = Date.now();
    }
  });

  // ── UNLOAD — final flush with sendBeacon
  function unloadFlush() {
    if (!document.hidden) activeMs += Date.now() - lastVisible;
    updateScroll();
    enqueue({
      type: 'page_unload',
      scroll_pct: maxScrollPct,
      meta: { time_active_ms: activeMs, time_total_ms: Date.now() - startMs },
    });
    send({ beacon: true });
  }
  window.addEventListener('pagehide', unloadFlush);
  window.addEventListener('beforeunload', unloadFlush);

  // ── PUBLIC API — named events
  window.shTrack = function (name, meta) {
    enqueue({ type: name, meta: meta || {} });
  };

  // ── Auto-fire some named events on common interactions
  document.addEventListener('click', function (e) {
    var t = e.target;
    if (!t) return;
    // Verse tooltip opens
    if (t.closest && t.closest('.verse-link')) {
      var ref = t.closest('.verse-link').dataset.ref;
      enqueue({ type: 'verse_tooltip_open', meta: { ref: ref || null } });
    }
    // Q&A panel opens (Find Peace sermon page — markup uses .qa-item)
    var qa = t.closest && t.closest('.qa-item, .qa-row, [data-qa]');
    if (qa) {
      var qaId = qa.dataset.qaId || qa.dataset.qa || null;
      var q = qa.querySelector && qa.querySelector('.qa-q');
      var qText = q ? q.textContent.trim().slice(0, 80) : null;
      enqueue({ type: 'qa_open', meta: { qa_id: qaId, q: qText } });
    }
    // Live CTA
    if (t.closest && t.closest('[data-live-cta]')) {
      enqueue({ type: 'live_cta_click', meta: {} });
    }
    // PDF download links
    var pdf = t.closest && t.closest('a[href*="/pdf"], a[href$=".pdf"]');
    if (pdf) {
      enqueue({ type: 'pdf_download', meta: { href: pdf.href.slice(0, 200) } });
    }
  }, { passive: true, capture: true });

  // ── Audio play tracking (Find Peace sermons + lessons)
  document.addEventListener('play', function (e) {
    var el = e.target;
    if (!el || el.tagName !== 'AUDIO') return;
    var src = (el.currentSrc || el.src || '').slice(0, 200);
    var timer30 = setTimeout(function () {
      enqueue({ type: 'audio_play_30s', meta: { src: src } });
    }, 30000);
    el.addEventListener('pause', function () { clearTimeout(timer30); }, { once: true });
    el.addEventListener('ended', function () {
      enqueue({ type: 'audio_play_complete', meta: { src: src } });
    }, { once: true });
  }, { passive: true, capture: true });

  // Initial scroll measurement
  updateScroll();
})();
</script>
@endif
