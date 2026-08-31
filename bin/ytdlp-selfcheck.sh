#!/bin/bash
# Canary for the Peace pipeline's YouTube dependency.
#
# The 403s that stalled peace:scan-channel through August came from yt-dlp
# going stale -- YouTube rotates the player clients it impersonates, and an old
# extractor keeps reading metadata fine while the media URL starts refusing.
# AudioExtractor now self-repairs that mid-run, but this still runs weekly so
# the repair happens on a quiet Monday rather than during Sabbath publishing.
#
# Deliberately plain bash and a plain JSON file -- no artisan, no database. If
# Laravel is broken, this still runs and still records what it found.

LOG=/home/shalom/logs/ytdlp-selfcheck.log
STATUS=/home/shalom/logs/ytdlp-status.json
OUT=/home/shalom/tmp/ytdlp-canary
mkdir -p "$(dirname "$LOG")" /home/shalom/tmp
rm -f "$OUT".*

TS=$(date '+%Y-%m-%d %H:%M:%S')
ISO=$(date '+%Y-%m-%dT%H:%M:%S%z')
VER=$(/usr/local/bin/yt-dlp --version 2>/dev/null)

write_status() {
  printf '{"state":"%s","version":"%s","checked_at":"%s","note":"%s"}\n' \
    "$1" "$VER" "$ISO" "$2" > "$STATUS"
  chmod 644 "$STATUS"
}

attempt() {
  timeout 150 /home/shalom/bin/ytdlp-auth \
    -f 'worstaudio' --max-filesize 4M \
    -o "$OUT.%(ext)s" \
    'https://www.youtube.com/watch?v=Rm49VQrs1uQ' >/dev/null 2>&1
  ls "$OUT".* >/dev/null 2>&1
}

if attempt; then
  echo "$TS  OK       yt-dlp $VER" >> "$LOG"
  write_status ok "download succeeded"
  rm -f "$OUT".*
  exit 0
fi

# Failed. Try the same repair AudioExtractor would do, so the pipeline finds a
# working yt-dlp already in place rather than discovering the rot itself.
rm -f "$OUT".*
/usr/bin/python3.11 -m pip install --upgrade --quiet yt-dlp >/dev/null 2>&1
NEWVER=$(/usr/local/bin/yt-dlp --version 2>/dev/null)

if [ -n "$NEWVER" ] && [ "$NEWVER" != "$VER" ] && attempt; then
  echo "$TS  REPAIRED yt-dlp $VER -> $NEWVER" >> "$LOG"
  VER="$NEWVER"
  write_status repaired "upgraded from $VER and download succeeded"
  rm -f "$OUT".*
  exit 0
fi

VER="${NEWVER:-$VER}"
echo "$TS  FAILED   yt-dlp $VER -- upgrade did not fix it" >> "$LOG"
write_status failed "upgrade did not fix it -- needs a human"
rm -f "$OUT".*

mail -s "[Shalom] yt-dlp canary FAILED" contact@c-wellpics.com <<MSG 2>/dev/null
The yt-dlp canary failed on $(hostname), and upgrading did not fix it.

Installed version: $VER

This is the one case the pipeline cannot repair on its own. Sermon audio
extraction will fail until it is sorted, which means peace:scan-channel
will hold sermons at the validation gate instead of publishing them.

Worth checking:
  - has YouTube changed something yt-dlp has not caught up with yet?
  - is the box being rate limited or blocked?
  - try by hand:
      sudo -u shalom /home/shalom/bin/ytdlp-auth -f worstaudio \\
        -o /tmp/t.%(ext)s 'https://www.youtube.com/watch?v=Rm49VQrs1uQ'

Log: $LOG
MSG

exit 1
