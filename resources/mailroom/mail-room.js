/* ── Mail room ──────────────────────────────────────────────────────────────
   The Alpine component behind resources/views/admin/mail.blade.php.

   It holds no mail of its own. Everything comes from /admin/mail/api, which
   reads the copy of the mailbox that the scheduler keeps in the database —
   the web process cannot talk to Dovecot (exec is disabled in the FPM pools,
   deliberately), so anything that changes a message is queued as an intent
   and applied within the minute.

   Loaded as a classic script so it registers before Alpine, which arrives as
   a deferred module from the Vite bundle.
   ────────────────────────────────────────────────────────────────────────── */

(() => {
  const LABEL = { person: 'Person', update: 'Update', receipt: 'Receipt', unknown: 'Mail' };

  const svg = (paths) =>
    `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" ` +
    `stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${paths}</svg>`;

  const BOX_ICON = {
    media:     '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"/><circle cx="12" cy="13" r="3.2"/>',
    hello:     '<path d="M13 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7"/><path d="m16 16 4-4-4-4"/><path d="M20 12H10"/>',
    prayer:    '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/>',
    treasurer: '<path d="M4 3h13l3 3v15l-2.5-1.6L15 21l-2.5-1.6L10 21l-2.5-1.6L5 21l-1-.6Z"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/>',
    app:       '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1 1.56V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.9 19.3a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.88 1.7 1.7 0 0 0-1.56-1H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.7 8.9a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.88.34H9a1.7 1.7 0 0 0 1-1.56V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.88V9a1.7 1.7 0 0 0 1.56 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51 1Z"/>',
  };

  // Keyed by Dovecot's own folder names, which are namespaced under INBOX
  // on this server — not by the pretty label.
  const FOLDER_ICON = {
    'INBOX':   '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
    'INBOX.Archive': '<rect x="2" y="4" width="20" height="5" rx="1"/><path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/>',
    'INBOX.Sent':    '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
    'INBOX.Trash':   '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
  };


  // ── Faces ───────────────────────────────────────────────────────────────
  // Drawn locally from the name and address. No avatar service: fetching a
  // picture means telling somebody else who writes to this church, and the
  // circle is not worth that. The hue comes from the address, so a person
  // keeps the same colour everywhere they appear.

  const hue = (str) => {
    let h = 0;
    for (let i = 0; i < str.length; i++) h = (h * 31 + str.charCodeAt(i)) % 360;
    return h;
  };

  // Bulk subdomains are not the brand — order.homedepot.com is Home Depot,
  // and mail.paypal.com is PayPal.
  const BULK_SUB = /^(order|orders|email|emails|mail|mailer|news|newsletter|connect|connected|notify|notification|reply|e|em|info|marketing|campaign)\./;

  /** The brand a machine writes on behalf of: the registrable part of its domain. */
  const brand = (addr) => {
    const domain = String(addr || '').split('@')[1] || '';
    const bare = domain.replace(BULK_SUB, '');
    const parts = bare.split('.').filter(Boolean);

    // Drop the TLD, and the country second-level if there is one (.co.uk).
    const cut = parts.length > 2 && parts[parts.length - 2].length <= 3 ? 2 : 1;

    return parts.slice(0, Math.max(1, parts.length - cut)).pop() || bare || '?';
  };

  const initials = (name, addr) => {
    const clean = String(name || '').replace(/["']/g, '').trim();
    const words = clean.split(/\s+/).filter((w) => /[a-z]/i.test(w));
    if (words.length >= 2) return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    if (words.length === 1 && words[0].length > 1) return words[0].slice(0, 2).toUpperCase();
    const local = String(addr || '?').split('@')[0];
    return (local.slice(0, 2) || '?').toUpperCase();
  };

  const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (c) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

  const VALID = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

  const post = (url, body) =>
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });

  window.mailroom = (boxes, folders) => ({
    boxes,
    folders,

    box: 'media',
    folder: 'INBOX',
    filter: 'all',
    query: '',

    items: [],
    tally: { all: 0, person: 0, update: 0, receipt: 0 },
    unread: 0,
    searchLine: '',
    loading: false,

    sel: null,
    showImages: false,
    zoom: 'fit',        // 'fit' scales the mail to the room, 'full' is actual size
    frameWide: false,   // true when the mail is wider than the room and had to be scaled
    reading: false,
    listonly: false,
    picker: false,

    recips: [],
    draftTo: '',
    draftBody: '',
    badTo: false,

    boot() {
      this.load();
      // The picker anchors to the control that opened it, the way a menu
      // should come out of the thing you pressed.
      this.$watch('picker', (open) => { if (open) this.anchorPicker(); });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { this.picker = false; }
      });
    },

    get unreadTotal() {
      return this.boxes.reduce((n, b) => n + b.unread, 0);
    },

    get countLine() {
      if (this.query.trim()) return 'searching all';
      return this.unread ? this.unread + ' unread' : this.tally.all + ' messages';
    },

    get paragraphs() {
      return String(this.sel?.body ?? '').split(/\n{2,}/).map((p) => p.trim()).filter(Boolean);
    },

    get firstName() {
      return String(this.sel?.who ?? '').split(/[\s<]/)[0] || 'them';
    },

    get frameDoc() {
      if (!this.sel?.html) return '';
      // Remote images arrive as data-src and only become src when asked for.
      const body = this.showImages ? this.sel.html.replace(/data-src=/g, 'src=') : this.sel.html;

      // A held-back image should read as absent, not as broken — the torn-page
      // icon makes a perfectly good message look damaged. Links open outside
      // the frame, since nothing inside it should be able to navigate us.
      return '<base target="_blank" rel="noopener">' +
        '<style>img[data-src]{display:none!important}' +
        'html{-webkit-text-size-adjust:100%}body{margin:0}</style>' + body;
    },

    /**
     * Fit the mail to the screen.
     *
     * Marketing mail is built for a desktop — a 560 or 600 pixel table that
     * will not reflow, because it was never written to. On a phone that is
     * wider than the room, and Safari will not let you pinch below the initial
     * scale, so there is no way out of it by hand. So we do what a mail app
     * does: measure the mail's real width and scale the whole thing down until
     * it fits, which is why the frame is same-origin.
     *
     * Tapping switches to actual size, where it scrolls sideways inside its
     * own box and never widens the page.
     */
    fitFrame(el) {
      const measure = () => {
        try {
          const doc = el.contentDocument;
          const body = doc?.body;
          if (!body) return;

          // Undo any previous scaling before measuring, or each pass would
          // measure the last pass's result.
          el.style.transform = '';
          el.style.width = '100%';

          const natural = Math.max(
            body.scrollWidth,
            doc.documentElement.scrollWidth,
            ...[...body.querySelectorAll('table, img')].map((n) => n.offsetWidth || 0)
          );

          const wrap = el.parentElement;
          const pad = parseFloat(getComputedStyle(wrap).paddingTop)
            + parseFloat(getComputedStyle(wrap).paddingBottom);
          const room = wrap.clientWidth;
          const fits = !natural || natural <= room + 2;

          this.frameWide = !fits;

          if (fits || this.zoom === 'full') {
            el.style.width = fits ? '100%' : natural + 'px';
            el.style.height = Math.min(Math.max(body.scrollHeight + 24, 200), 6000) + 'px';
            wrap.style.height = '';

            return;
          }

          // Scale the frame itself, anchored top-left, and give the box the
          // scaled height so nothing below it is pushed down by empty space.
          const k = room / natural;
          const h = Math.min(Math.max(body.scrollHeight + 16, 200), 6000);

          el.style.width = natural + 'px';
          el.style.height = h + 'px';
          el.style.transformOrigin = 'top left';
          el.style.transform = 'scale(' + k + ')';
          // The mat is not part of the mail, so it is added on top of the
          // scaled height rather than eating into it.
          wrap.style.height = Math.ceil(h * k + pad) + 'px';
        } catch (e) {
          /* opaque frame — the fixed height stands */
        }
      };

      el.addEventListener('load', () => setTimeout(measure, 0));
      // Images arriving late change the height, and so does turning the phone.
      this.$watch('zoom', () => setTimeout(measure, 0));
      this.$watch('showImages', () => setTimeout(measure, 60));
      window.addEventListener('resize', measure);
      setTimeout(measure, 0);
    },

    kindLabel(k) { return LABEL[k] || LABEL.unknown; },
    filterLabel(f) { return { all: 'All', person: 'People', update: 'Updates', receipt: 'Receipts' }[f]; },
    boxIcon(id) { return svg(BOX_ICON[id.replace(/-old$/, '')] || BOX_ICON.app); },
    folderIcon(key) { return svg(FOLDER_ICON[key] || FOLDER_ICON.INBOX); },

    /**
     * The face is the one thing on the row that says WHO. It does not repeat
     * the kind — the UPDATE / RECEIPT chip beside it already does that, and a
     * column of identical robots is decoration, not information.
     *
     * A person gets their initials. A machine gets its brand's monogram, taken
     * from the domain so every message from B&H looks like B&H whether it came
     * from bhphoto.com or bhphotovideo.com.
     */
    faceInner(m) {
      if (m.kind === 'person') return esc(initials(m.who, m.addr));

      return esc(brand(m.addr).slice(0, 2).toUpperCase());
    },

    fileIcon(kind) {
      return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" '
        + 'stroke-linecap="round" stroke-linejoin="round">'
        + (FILE_ICON[kind] || FILE_ICON.file) + '</svg>';
    },

    /** Is there anything to read, or is the message only its attachments? */
    get hasBody() {
      const h = String(this.sel?.html ?? '');
      if (!h) return String(this.sel?.body ?? '').trim().length > 0;

      // Strip the frame's own preamble before deciding it is empty.
      return h.replace(/<[^>]+>/g, '').replace(/\s|&nbsp;/g, '').length > 0;
    },

    /** Colour keyed to the person, or to the brand — so a sender is
        recognisable by colour before you have read the name. */
    faceStyle(m) {
      // For a brand the colour keys off the monogram, not the domain, so
      // bhphoto.com and bhphotovideo.com read as one sender — which is what
      // they are. Same letters, same colour.
      const key = m.kind === 'person'
        ? String(m.addr || m.who || '').toLowerCase()
        : this.faceInner(m);

      return '--h:' + hue(key);
    },

    async load() {
      this.loading = true;
      const p = new URLSearchParams({ box: this.box, folder: this.folder, filter: this.filter });
      if (this.query.trim()) p.set('q', this.query.trim());

      const r = await fetch('/admin/mail/api/messages?' + p, { headers: { Accept: 'application/json' } });
      const d = await r.json();

      this.items = d.items ?? [];

      if (this.query.trim()) {
        const bits = (d.ops ?? []).map((o) => `<b>${esc(o.field)}</b> is ${esc(o.value)}`);
        if (d.fixes?.length) bits.push(`also looked for <b>${d.fixes.map(esc).join('</b>, <b>')}</b>`);
        this.searchLine = bits.length
          ? `${this.items.length} found — ${bits.join(' · ')}`
          : `${this.items.length} found across ${d.boxes} mailbox${d.boxes === 1 ? '' : 'es'}`;
      } else {
        this.searchLine = '';
        this.tally = d.tally;
        this.unread = d.unread;
      }

      this.loading = false;
    },

    async open(m) {
      this.showImages = false;
      this.zoom = 'fit';
      this.frameWide = false;
      this.reading = true;
      this.listonly = false;
      this.box = m.box;

      const r = await fetch('/admin/mail/api/message/' + m.id, { headers: { Accept: 'application/json' } });
      this.sel = await r.json();

      this.recips = this.sel.addr ? [this.sel.addr] : [];
      this.draftTo = '';
      this.draftBody = '';

      if (!m.seen) {
        m.seen = true;
        this.unread = Math.max(0, this.unread - 1);
        const b = this.boxes.find((x) => x.id === m.box);
        if (b) b.unread = Math.max(0, b.unread - 1);
        post('/admin/mail/api/act', { id: m.id, action: 'seen' });
      }
    },

    close() {
      this.reading = false;
      this.sel = null;
    },

    /** Archive and trash move the message; the list should not pretend otherwise. */
    async act(action) {
      const id = this.sel.id;
      await post('/admin/mail/api/act', { id, action });
      this.items = this.items.filter((m) => m.id !== id);
      this.close();
      this.load();
    },

    setBox(id) {
      this.box = id;
      this.picker = false;
      this.close();
      this.load();
    },

    setFolder(key) {
      this.folder = key;
      this.close();
      this.load();
    },

    anchorPicker() {
      const btn = document.getElementById('openpicker');
      const pick = this.$root.querySelector('.picker');
      if (!btn || !pick) return;
      const r = btn.getBoundingClientRect();
      pick.style.left = Math.max(12, r.left) + 'px';
      pick.style.top = r.bottom + 8 + 'px';
    },

    /** Highlight what was searched for, and nothing else. */
    hi(text) {
      const out = esc(text);
      const q = this.query.trim();
      if (!q) return out;
      const terms = q.toLowerCase().replace(/\w+:/g, ' ').replace(/[^a-z0-9 ]/g, ' ')
        .split(/\s+/).filter((t) => t.length >= 2);
      return terms.reduce((s, t) =>
        s.replace(new RegExp('(' + t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<mark>$1</mark>'), out);
    },

    // ── The recipient field ─────────────────────────────────────────────────
    // Every mail app turns what you typed into a chip you can no longer edit.
    // Here a chip opens back into text, with the caret near where you tapped.

    editChip(e, i) {
      const text = this.recips[i];
      const box = e.currentTarget.querySelector('.txt').getBoundingClientRect();
      const ratio = Math.min(1, Math.max(0, (e.clientX - box.left) / box.width));
      const caret = Math.round(ratio * text.length);

      this.recips.splice(i, 1);
      this.draftTo = text;
      this.$nextTick(() => {
        this.$refs.to.focus();
        this.$refs.to.setSelectionRange(caret, caret);
      });
    },

    commitTo() {
      const v = this.draftTo.trim().replace(/[;,]+$/, '');
      if (!v) return true;
      if (!VALID.test(v)) { this.badTo = true; return false; }
      this.recips.push(v);
      this.draftTo = '';
      this.badTo = false;
      return true;
    },

    onToInput() {
      this.badTo = false;
      // A separator means "done with that one" — chip it and carry on. Never
      // mid-typing: half an address becoming a chip is the bug this fixes.
      if (/[,;\s]$/.test(this.draftTo)) {
        const candidate = this.draftTo.trim().replace(/[;,]+$/, '');
        if (VALID.test(candidate)) { this.draftTo = candidate; this.commitTo(); }
      }
    },

    onToBackspace(e) {
      // Backspace on an empty field opens the last chip rather than eating it.
      if (this.draftTo !== '' || !this.recips.length) return;
      e.preventDefault();
      const last = this.recips.pop();
      this.draftTo = last;
      this.$nextTick(() => {
        this.$refs.to.focus();
        this.$refs.to.setSelectionRange(last.length, last.length);
      });
    },

    onToPaste(e) {
      // Paste a blob: chip what is valid, leave the rest as text so it can be
      // fixed in place instead of silently vanishing.
      const txt = (e.clipboardData || window.clipboardData).getData('text');
      if (!txt || !/[,;\n]/.test(txt)) return;
      e.preventDefault();
      const bad = [];
      txt.split(/[,;\n]+/).map((t) => t.trim()).filter(Boolean)
        .forEach((t) => (VALID.test(t) ? this.recips.push(t) : bad.push(t)));
      this.draftTo = bad.join(', ');
      this.badTo = bad.length > 0;
    },
  });

  // Theme is a per-person choice, remembered.
  document.addEventListener('alpine:init', () => {
    const saved = localStorage.getItem('shalom-mail-theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);

    Alpine.store('mailTheme', {
      // Held in the store, not read back off the attribute — a getter over
      // the DOM is not reactive and the button label would go stale.
      mode: saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),

      get label() { return this.mode === 'dark' ? 'Light' : 'Dark'; },

      flip() {
        this.mode = this.mode === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', this.mode);
        localStorage.setItem('shalom-mail-theme', this.mode);
      },
    });
  });
})();
