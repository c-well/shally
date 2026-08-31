#!/bin/bash
# Weekly canary for the Peace pipeline's YouTube dependency.
#
# The 403s that stalled peace:scan-channel through August came from yt-dlp
# going stale -- YouTube rotates its player clients, and an old extractor
# starts handing back URLs that refuse to serve. Nothing in the app can see
# that coming, so we check for it on a schedule rather than finding out when
# a sermon quietly fails to publish.
#
# Pulls a small slice of a known-good public video. If that fails, yt-dlp
# needs updating:  python3.11 -m pip install --upgrade yt-dlp

LOG=/home/shalom/logs/ytdlp-selfcheck.log
OUT=/home/shalom/tmp/ytdlp-canary
mkdir -p "$(dirname "$LOG")" /home/shalom/tmp
rm -f "$OUT".*

TS=$(date '+%Y-%m-%d %H:%M:%S')
VER=$(/usr/local/bin/yt-dlp --version 2>/dev/null)

timeout 150 /home/shalom/bin/ytdlp-auth \
  -f 'worstaudio' --max-filesize 4M \
  -o "$OUT.%(ext)s" \
  'https://www.youtube.com/watch?v=Rm49VQrs1uQ' >/dev/null 2>&1

if ls "$OUT".* >/dev/null 2>&1; then
  echo "$TS  OK      yt-dlp $VER" >> "$LOG"
  rm -f "$OUT".*
  exit 0
fi

echo "$TS  FAILED  yt-dlp $VER -- run: python3.11 -m pip install --upgrade yt-dlp" >> "$LOG"

mail -s "[Shalom] yt-dlp canary FAILED" contact@c-wellpics.com <<MSG 2>/dev/null
The yt-dlp canary failed on $(hostname).

Installed version: $VER

The Peace sermon pipeline (peace:scan-channel) will stop publishing
until this is sorted -- audio downloads will 403.

Fix:
  python3.11 -m pip install --upgrade yt-dlp

Then re-run:
  sudo -u shalom /opt/cpanel/ea-php84/root/usr/bin/php \\
    /home/shalom/laravel/artisan peace:scan-channel

Log: $LOG
MSG

rm -f "$OUT".*
exit 1
