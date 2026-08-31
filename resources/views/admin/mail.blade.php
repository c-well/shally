<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Mail — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
@include('partials.theme-vars')
<link rel="stylesheet" href="{{ asset('css/mail.css') }}?v={{ filemtime(public_path('css/mail.css')) }}">
{{-- Classic script first so it registers window.mailroom before Alpine,
     which arrives deferred from the Vite bundle, starts looking for it. --}}
<script src="{{ asset('js/mail-room.js') }}?v={{ filemtime(public_path('js/mail-room.js')) }}"></script>
@vite(['resources/js/app.js'])
</head>
<body>

<div class="pagehead">
  <a class="crumb" href="{{ route('admin.hub') }}">&larr; Admin</a>
  <h1>Mail</h1>
  <button class="themetoggle" x-data x-on:click="$store.mailTheme.flip()" x-text="$store.mailTheme.label">Dark</button>
</div>

<div x-data="mailroom(@js($boxes), @js($folders))" x-init="boot()">

  <div class="shell" id="shell" :class="{ reading: reading, listonly: listonly }">

    {{-- Zone one: where mail lives. Folders, not features. --}}
    <nav class="rail">
      <div class="mark">SH</div>

      <template x-for="(label, key) in folders" :key="key">
        <button class="railbtn" :class="{ on: folder === key }" :title="label" :aria-label="label"
                x-on:click="setFolder(key)">
          <span x-html="folderIcon(key)"></span>
          <span class="dot" x-show="key === 'INBOX' && unreadTotal > 0"></span>
        </button>
      </template>

      <div class="railspacer"></div>

      <button class="railbtn" x-on:click="listonly = !listonly; if (listonly) reading = false"
              :title="listonly ? 'Show reading pane' : 'Hide reading pane'"
              :aria-label="listonly ? 'Show reading pane' : 'Hide reading pane'">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14h6v6"/><path d="M20 10h-6V4"/><path d="M14 10 21 3"/><path d="M3 21l7-7"/></svg>
      </button>
    </nav>

    {{-- Zone two: the list. --}}
    <section class="list">
      <div class="listhead">
        <button class="boxswitch" id="openpicker" x-on:click="picker = !picker"
                :aria-expanded="picker.toString()" aria-haspopup="dialog">
          <span class="name" x-text="box"></span>
          <span class="chev">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </span>
          <span class="count" x-text="countLine"></span>
        </button>

        <div class="searchwrap">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          <input type="search" x-model="query" x-on:input.debounce.140ms="load()"
                 placeholder="Search, or from: subject: has:pdf"
                 autocapitalize="off" autocorrect="off" spellcheck="false">
          <button class="sclear" x-show="query" x-on:click="query = ''; load()" aria-label="Clear search">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6 18 18M18 6 6 18"/></svg>
          </button>
        </div>

        <p class="sline" x-show="searchLine" x-html="searchLine"></p>

        <div class="filters" x-show="!query.trim()">
          <template x-for="f in ['all','person','update','receipt']" :key="f">
            <button class="filter" :class="{ on: filter === f, empty: !tally[f] && f !== 'all' }"
                    x-on:click="filter = f; load()">
              <span x-text="filterLabel(f)"></span>
              <span class="n">
                <span x-text="tally[f] ?? 0"></span>
                <span class="unit" x-show="f === 'all' && unread" x-text="' · ' + unread + ' unread'"></span>
              </span>
            </button>
          </template>
        </div>
      </div>

      <div class="msgs">
        <template x-for="m in items" :key="m.id">
          <button class="msg" :class="{ seen: m.seen, on: sel && sel.id === m.id }"
                  x-on:pointerdown="open(m)">
            <span class="row1">
              <span class="who" x-html="hi(m.who)"></span>
              <span class="kind" :class="m.kind" x-show="!query.trim()" x-text="kindLabel(m.kind)"></span>
              <span class="rbox" x-show="query.trim()" x-text="m.box"></span>
              <span class="when" x-text="m.when"></span>
            </span>
            <span class="subj" x-html="hi(m.subj)"></span>
            <span class="prev" x-html="hi(m.prev)"></span>
          </button>
        </template>

        <div class="empty" style="min-height:9rem" x-show="!items.length && !loading">
          <div class="sub" x-text="query.trim() ? 'Nothing matches. Try fewer words.' : 'Nothing in this filter.'"></div>
        </div>
      </div>
    </section>

    {{-- Zone three: reading. --}}
    <main class="read">
      <div class="empty" x-show="!sel">
        <div>
          <div class="big">Nothing open</div>
          <div class="sub">Pick a message to read it here.</div>
        </div>
      </div>

      <template x-if="sel">
        <div style="display:contents">
          <div class="readbar">
            <button class="act backbtn" x-on:click="close()" :aria-label="'Back to ' + sel.box">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <span class="box" x-text="sel.box"></span>
            <span class="kind" :class="sel.kind" x-text="kindLabel(sel.kind)" :title="sel.reason"></span>
            <span class="acts">
              <button class="act" title="Reply" aria-label="Reply" x-on:click="$refs.reply?.focus()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
              </button>
              <button class="act" title="Archive" aria-label="Archive" x-on:click="act('archive')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="5" rx="1"/><path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/></svg>
              </button>
              <button class="act danger" title="Move to Trash" aria-label="Move to Trash" x-on:click="act('trash')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
              </button>
            </span>
          </div>

          <div class="readbody">
            <h2 x-text="sel.subj"></h2>
            <div class="fromline">
              <span x-text="sel.who"></span>
              <span class="addr" x-text="sel.addr"></span>
              <span class="stamp" x-text="sel.when"></span>
            </div>

            {{-- Plain text: our own markup, our own type. --}}
            <div class="prose" x-show="!sel.html">
              <template x-for="(p, i) in paragraphs" :key="i"><p x-text="p"></p></template>
            </div>

            {{-- HTML mail: somebody else's markup, kept in a sandbox, with
                 remote images held back until asked for — which is also what
                 holds back the tracking pixel. --}}
            <div class="htmlwrap" x-show="sel.html">
              <span class="imgnote" x-show="!showImages">Images held back</span>
              <button class="imgchip" :class="{ on: showImages }" x-on:click="showImages = true"
                      :title="showImages ? 'Images are showing' : 'Show images'"
                      :aria-label="showImages ? 'Images are showing' : 'Show images'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2"/>
                  <circle cx="8.5" cy="8.5" r="1.6"/>
                  <path d="m21 15-4.5-4.5L7 20"/>
                  <path class="slash" d="M3 21 21 3"/>
                </svg>
              </button>
              {{-- allow-same-origin only, so the frame can be measured and
                   sized to the mail. Scripts stay blocked — without
                   allow-scripts nothing in here can run. --}}
              <iframe sandbox="allow-same-origin" :srcdoc="frameDoc" style="height:460px"
                      x-init="fitFrame($el)" title="Message content"></iframe>
            </div>

            {{-- Reply. The recipient field is the whole point: a chip opens
                 back into text, so fixing a typo costs a tap, not a retype. --}}
            <div class="reply">
              <div class="tofield" x-on:pointerdown.self="$refs.to.focus()">
                <span class="tolabel">To</span>
                <div class="tochips" x-on:pointerdown.self="$refs.to.focus()">
                  <template x-for="(r, i) in recips" :key="i">
                    <span class="chip" title="Tap to edit" x-on:pointerdown.prevent="editChip($event, i)">
                      <span class="txt" x-text="r"></span>
                      <button class="x" tabindex="-1" :aria-label="'Remove ' + r"
                              x-on:click.stop="recips.splice(i, 1)">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6 18 18M18 6 6 18"/></svg>
                      </button>
                    </span>
                  </template>
                  <input class="toinput" x-ref="to" type="email" inputmode="email"
                         :class="{ bad: badTo }" :placeholder="recips.length ? '' : 'name@example.org'"
                         autocapitalize="off" autocorrect="off" spellcheck="false"
                         x-model="draftTo"
                         x-on:input="onToInput()"
                         x-on:keydown.enter.prevent="commitTo()"
                         x-on:keydown.tab="if (draftTo.trim()) { $event.preventDefault(); commitTo(); }"
                         x-on:keydown.backspace="onToBackspace($event)"
                         x-on:blur="commitTo()"
                         x-on:paste="onToPaste($event)">
                </div>
              </div>
              <textarea x-ref="reply" x-model="draftBody" :placeholder="'Write back to ' + firstName + '…'"></textarea>
              <div class="replyfoot">
                <button class="btn quiet" type="button" disabled title="Attachments land in a later pass">Attach</button>
                <span class="spacer"></span>
                <button class="btn" type="button" disabled title="Sending lands in a later pass">Send reply</button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </main>
  </div>

  {{-- The mailbox picker, anchored to the control that opened it. --}}
  <div class="scrim" :class="{ open: picker }" x-on:click="picker = false"></div>
  <div class="picker" :class="{ open: picker }" role="dialog" aria-label="Choose a mailbox">
    <div class="pickhead">Mailboxes</div>
    <div class="pickitems">
      <template x-for="b in boxes" :key="b.id">
        <button class="pickitem" :class="{ on: b.id === box }" x-on:click="setBox(b.id)">
          <span class="glyph" x-html="boxIcon(b.id)"></span>
          <span class="lbl"><b x-text="b.id"></b><span x-text="b.note"></span></span>
          <span class="n" :class="{ unread: b.unread }" x-text="b.unread ? b.unread + ' new' : b.total"></span>
        </button>
      </template>
    </div>
  </div>

  <p class="note">
    <b>How it works.</b>
    <span>Mail is read off the server on the scheduler with <code>doveadm</code>, so the room
    opens with no wait. Marking read, archiving and deleting are queued and applied within
    the minute — the web process cannot touch the mail store directly, which is deliberate.</span>
  </p>
</div>

</body>
</html>
