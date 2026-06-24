#!/usr/bin/env node
/**
 * ALIGNMENT / LAYOUT AUDIT — headless, no eyeballing.
 *
 * Loads each page in headless Chrome (via puppeteer-core + the system Chrome)
 * and reports layout problems in TEXT — the same kind of thing you'd otherwise
 * catch only by squinting at the screen:
 *
 *   1. HORIZONTAL OVERFLOW — any element whose right edge exceeds the viewport
 *      (the #1 cause of unwanted mobile side-scroll)
 *   2. LEFT-EDGE DRIFT — repeated components (same class) that should share a
 *      left edge but don't (this is the 3px ".msg.unread" bug class)
 *   3. TINY TAP TARGETS — links/buttons under 44x44 on mobile
 *   4. OFF-CANVAS — elements pushed off the left/right edge
 *
 * Runs at desktop (1280) and mobile (390) widths. Public pages need no auth;
 * to audit admin pages, pass a session cookie via SH_COOKIE env.
 *
 * Usage:
 *   node audit.mjs                       # audits the default public page list
 *   node audit.mjs https://site/x /y     # audit specific paths
 *   SH_COOKIE="church-of-peace-session=..." node audit.mjs /admin/messages
 */
import puppeteer from 'puppeteer-core';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE = 'https://thechurchofpeace.org';
const PATHS = process.argv.slice(2).filter(a => a.startsWith('/') || a.startsWith('http'));
const TARGETS = PATHS.length ? PATHS : [
  '/', '/find-peace', '/find-peace/not-your-address-hiRMwR',
  '/bible', '/hymnal', '/peace-notes', '/about', '/visit', '/schedule',
];
const WIDTHS = [{ name: 'desktop', w: 1280, h: 900 }, { name: 'mobile', w: 390, h: 844 }];

// In-page collector — runs in the browser, returns layout findings.
const COLLECT = (vw) => {
  const findings = { overflow: [], drift: [], tap: [], offcanvas: [] };
  const sel = (el) => {
    let s = el.tagName.toLowerCase();
    if (el.id) return s + '#' + el.id;
    if (el.className && typeof el.className === 'string') {
      const c = el.className.trim().split(/\s+/).slice(0, 2).join('.');
      if (c) s += '.' + c;
    }
    return s;
  };
  const all = Array.from(document.querySelectorAll('body *'));

  // An element only causes a REAL overflow problem if nothing clips it. A
  // carousel/slider track with overflow:hidden legitimately holds off-canvas
  // slides — those don't scroll the page, so skip anything an ancestor clips.
  const isClipped = (el) => {
    let p = el.parentElement;
    while (p && p !== document.body) {
      const o = getComputedStyle(p);
      if (/(hidden|auto|scroll|clip)/.test(o.overflow + o.overflowX + o.overflowY)) return true;
      p = p.parentElement;
    }
    return false;
  };

  // 1 + 4: overflow / off-canvas (only if not clipped by an ancestor)
  for (const el of all) {
    const r = el.getBoundingClientRect();
    if (r.width === 0 || r.height === 0) continue;
    const style = getComputedStyle(el);
    if (style.position === 'fixed') continue; // fixed overlays legitimately span
    if (isClipped(el)) continue;              // carousel/slider/scroll containers
    if (r.right > vw + 1) findings.overflow.push({ sel: sel(el), right: Math.round(r.right), vw });
    if (r.left < -1)      findings.offcanvas.push({ sel: sel(el), left: Math.round(r.left) });
  }

  // 2: left-edge drift among same-class repeated elements
  const groups = {};
  for (const el of all) {
    if (!el.className || typeof el.className !== 'string') continue;
    const key = el.className.trim().split(/\s+/).sort().join('.');
    if (!key) continue;
    const r = el.getBoundingClientRect();
    if (r.width < 40 || r.height < 10) continue;
    (groups[key] ||= []).push(Math.round(r.left));
  }
  for (const [key, lefts] of Object.entries(groups)) {
    if (lefts.length < 2) continue;
    const min = Math.min(...lefts), max = Math.max(...lefts);
    if (max - min >= 2 && max - min <= 20) { // small drift = likely a bug, big = intentional layout
      findings.drift.push({ key, spread: max - min, count: lefts.length });
    }
  }

  // 3: tiny tap targets (mobile). Only flag genuinely tiny targets — BOTH
  // dimensions under 44 (icon buttons, bare links). Inline text links in a
  // paragraph are exempt from the 44px guideline (WCAG 2.5.5), so a wide-but-
  // short text link is not flagged.
  for (const el of document.querySelectorAll('a, button, [role=button]')) {
    const r = el.getBoundingClientRect();
    if (r.width === 0 || r.height === 0) continue;
    const txt = (el.innerText || '').trim();
    const isIconish = txt.length <= 2; // icon/empty link
    if ((r.width < 44 && r.height < 44) || (isIconish && (r.width < 44 || r.height < 44))) {
      findings.tap.push({ sel: sel(el), w: Math.round(r.width), h: Math.round(r.height), txt: txt.slice(0, 30) });
    }
  }
  return findings;
};

const cookie = process.env.SH_COOKIE || null;

const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox'] });
let totalIssues = 0;
const lines = [];
const log = (s) => { lines.push(s); console.log(s); };

log(`\n═══ ALIGNMENT AUDIT · ${new Date().toISOString()} ═══`);

for (const path of TARGETS) {
  const url = path.startsWith('http') ? path : BASE + path;
  log(`\n■ ${path}`);
  for (const vp of WIDTHS) {
    const page = await browser.newPage();
    await page.setViewport({ width: vp.w, height: vp.h });
    // Gentle on the shared host: block heavy sub-resources. CSS still loads
    // (layout stays accurate); images/fonts/media/analytics do not — this cuts
    // dozens of requests per page so we don't trip LiteSpeed's rate limiter.
    await page.setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149 Safari/537.36');
    await page.setRequestInterception(true);
    page.on('request', req => {
      const t = req.resourceType();
      if (t === 'image' || t === 'font' || t === 'media') req.abort();
      else req.continue();
    });
    if (cookie) {
      const [name, ...v] = cookie.split('=');
      await page.setCookie({ name, value: v.join('='), domain: new URL(BASE).hostname, path: '/' });
    }
    try {
      // Polite: shared host + rate limiter. Retry once on 429/503 after a pause.
      let resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
      let code = resp ? resp.status() : 0;
      if (code === 429 || code === 503) {
        await new Promise(r => setTimeout(r, 5000));
        resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        code = resp ? resp.status() : 0;
      }
      if (code >= 400) { log(`  [${vp.name}] HTTP ${code} — skipped`); await page.close(); await new Promise(r=>setTimeout(r,1500)); continue; }
      await new Promise(r => setTimeout(r, 400)); // let layout settle
      const f = await page.evaluate(COLLECT, vp.w);
      let issues = 0;
      // de-dup overflow by selector
      const ov = [...new Map(f.overflow.map(o => [o.sel, o])).values()];
      ov.forEach(o => { log(`  [${vp.name}] ⟶ OVERFLOW ${o.sel} right=${o.right}px > vw=${o.vw}px`); issues++; });
      const oc = [...new Map(f.offcanvas.map(o => [o.sel, o])).values()];
      oc.forEach(o => { log(`  [${vp.name}] ← OFF-CANVAS ${o.sel} left=${o.left}px`); issues++; });
      const dr = [...new Map(f.drift.map(d => [d.key, d])).values()];
      dr.forEach(d => { log(`  [${vp.name}] ↔ LEFT-EDGE DRIFT .${d.key} — ${d.spread}px across ${d.count} els`); issues++; });
      if (vp.name === 'mobile') {
        const tp = [...new Map(f.tap.map(t => [t.sel + t.txt, t])).values()];
        tp.forEach(t => { log(`  [mobile] ⊡ SMALL TAP ${t.sel} ${t.w}×${t.h} "${t.txt}"`); issues++; });
      }
      if (issues === 0) log(`  [${vp.name}] ✓ clean`);
      totalIssues += issues;
    } catch (e) {
      log(`  [${vp.name}] error: ${e.message.slice(0, 80)}`);
    }
    await page.close();
    await new Promise(r => setTimeout(r, 1200)); // polite gap between loads
  }
}

log(`\n═══ TOTAL ISSUES: ${totalIssues} ═══\n`);
await browser.close();
process.exit(0);
