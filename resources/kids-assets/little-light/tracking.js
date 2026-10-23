/* Little Light: first-party, aggregate chapter evidence. No names or raw inputs. */
(function (root) {
  'use strict';
  function createTracker(env) {
    const pending = new Map();
    let current = null, csrf = null, busy = false, lastInput = -Infinity;
    let browser;
    const uuid = () => env.crypto.randomUUID();
    try {
      const cached = JSON.parse(env.storage.getItem('little-light-browser') || 'null');
      browser = cached && cached.expires > env.now() && /^[0-9a-f-]{36}$/i.test(cached.id) ? cached.id : uuid();
      if (browser !== cached?.id) env.storage.setItem('little-light-browser', JSON.stringify({id: browser, expires: env.now() + 90 * 86400000}));
    } catch (_) { browser = uuid(); }
    const preview = new URLSearchParams(env.search).get('preview') === '1';
    const endpoint = '/api/little-light/tracking/';
    function begin(chapter) {
      if (current) return;
      const token = uuid();
      current = {token, chapter, started: false, ended: false, sequence: 0, revision: 1, ack: 0, seconds: 0,
        data: {active_seconds: 0, furthest_percent: 0, scrolls: 0, lights: 0, sheep: 0, stones: 0, stumbles: 0, status: 'playing'}};
      pending.set(token, current);
      if (pending.size > 12) pending.delete(pending.keys().next().value);
      lastInput = -Infinity;
      void flush();
    }
    function input() { lastInput = env.now(); }
    function sample(dt, data, hasInput = false) {
      if (!current || current.ended) return;
      if (hasInput) input();
      const previous = JSON.stringify(current.data), oldStatus = current.data.status;
      if (data.status === 'playing' && !env.hidden() && env.now() - lastInput < 5000) current.seconds += Math.max(0, Math.min(dt, .1));
      current.data.active_seconds = Math.min(86400, Math.floor(current.seconds));
      for (const key of ['furthest_percent', 'scrolls', 'lights', 'sheep', 'stones']) current.data[key] = Math.max(current.data[key], data[key] || 0);
      current.data.status = data.status;
      if (JSON.stringify(current.data) !== previous) current.revision++;
      if (oldStatus !== data.status) void flush();
    }
    function stumble() { if (current) { current.data.stumbles = Math.min(10000, current.data.stumbles + 1); current.revision++; } }
    function end() {
      if (!current) return;
      if (!['complete', 'retry'].includes(current.data.status)) current.data.status = 'left';
      current.ended = true; current.revision++; current = null; void flush();
    }
    async function post(path, body) {
      const response = await env.fetch(endpoint + path, {method: 'POST', credentials: 'same-origin', keepalive: true,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify(body)});
      if (response.status === 419) csrf = null;
      if (!response.ok) throw new Error('Tracking unavailable');
    }
    async function flush() {
      if (busy || !pending.size) return;
      busy = true;
      try {
        if (!csrf) {
          const r = await env.fetch(endpoint + 'bootstrap', {credentials: 'same-origin', cache: 'no-store', headers: {'Accept': 'application/json'}});
          if (!r.ok) return;
          csrf = (await r.json()).csrf;
          if (!csrf) return;
        }
        for (const run of pending.values()) {
          if (!run.started) {
            await post('start', {run_token: run.token, browser_token: browser, chapter: run.chapter, preview});
            run.started = true;
          }
          if (run.ack < run.revision) {
            const revision = run.revision;
            await post('progress', {...run.data, run_token: run.token, browser_token: browser, sequence: ++run.sequence});
            run.ack = revision;
          }
          if (run.ended && run.ack === run.revision) pending.delete(run.token);
        }
      } catch (_) {
        // Gameplay and local saves work even if analytics is offline or blocked.
        // Retry cumulative summaries on the next flush; duplicate reports cannot add time.
      } finally { busy = false; }
    }
    function suspend() {
      if (current && current.data.status === 'playing') { current.data.status = 'paused'; current.revision++; }
      lastInput = -Infinity; void flush();
    }
    return {begin, input, sample, stumble, end, flush, suspend};
  }
  if (typeof module !== 'undefined' && module.exports) module.exports = {createTracker};
  if (typeof window !== 'undefined') {
    try {
      root.LittleLightTracking = createTracker({fetch: window.fetch.bind(window), storage: {getItem: key => window.localStorage.getItem(key), setItem: (key, value) => window.localStorage.setItem(key, value)},
        crypto: window.crypto, now: Date.now, hidden: () => document.hidden, search: location.search});
      setInterval(() => root.LittleLightTracking.flush(), 15000);
      document.addEventListener('visibilitychange', () => { if (document.hidden) root.LittleLightTracking.suspend(); });
      window.addEventListener('pagehide', () => root.LittleLightTracking.suspend());
      window.addEventListener('online', () => root.LittleLightTracking.flush());
    } catch (_) { /* Tracking must never prevent a game from starting. */ }
  }
})(typeof window !== 'undefined' ? window : globalThis);
