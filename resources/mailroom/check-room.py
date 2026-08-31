#!/usr/bin/env python3
"""
Every function the blade calls must exist on the Alpine component.

Twice now a block edit to mail-room.js has swallowed a neighbouring method and
the room shipped with a dead rail or an empty avatar — Alpine logs the
ReferenceError to the console and renders nothing, so it reads as a styling
problem rather than a missing function. This catches it before deploy.

Deliberately narrow: only bare calls like `faceInner(m)`, not property reads
and not method calls on something else (`recips.splice(...)`).
"""
import re
import sys

blade = open(sys.argv[1]).read()
js = open(sys.argv[2]).read()
component = js.split('window.mailroom', 1)[1]

exprs = re.findall(
    r'(?:x-(?:text|html|show|if|init|model|for|on:[\w.]+)|:[\w-]+)="([^"]*)"', blade)

ALPINE = {'$el', '$refs', '$store', '$watch', '$nextTick', '$root', '$data', '$dispatch',
          'in', 'of', 'typeof', 'new'}   # keywords that can precede a paren

called = set()
for e in exprs:
    for name in re.findall(r'(?<![\w.$])([a-zA-Z_$][\w$]*)\s*\(', e):
        if name not in ALPINE:
            called.add(name)

missing = sorted(n for n in called
                 if not re.search(r'(?:^|[\s,{])' + re.escape(n) + r'\s*[(:]', component))

if missing:
    print('MISSING from the component: ' + ', '.join(missing))
    raise SystemExit(1)

print('blade and component agree — %d functions checked: %s'
      % (len(called), ', '.join(sorted(called))))
