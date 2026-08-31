<?php

namespace App\Http\Controllers;

use App\Models\MailAction;
use App\Models\MailAttachment;
use App\Models\MailMessage;
use App\Services\Mail\MailSearch;
use Illuminate\Http\Request;

class AdminMailController extends Controller
{
    public function index()
    {
        return view('admin.mail', [
            'boxes' => $this->boxes(),
            'folders' => config('mailroom.folders'),
        ]);
    }

    /** Name, note and counts for the mailbox picker. */
    public function boxes()
    {
        $counts = MailMessage::where('folder', 'INBOX')
            ->selectRaw('mailbox, COUNT(*) total, SUM(seen = 0) unread')
            ->groupBy('mailbox')->get()->keyBy('mailbox');

        return collect(config('mailroom.boxes'))->map(fn ($cfg, $id) => [
            'id' => $id,
            'addr' => $cfg['addr'],
            'note' => $cfg['note'],
            'total' => (int) ($counts[$id]->total ?? 0),
            'unread' => (int) ($counts[$id]->unread ?? 0),
        ])->values();
    }

    public function messages(Request $r, MailSearch $search)
    {
        $q = trim((string) $r->query('q', ''));

        // A query searches every mailbox; without one, the list is the mailbox.
        if ($q !== '') {
            return response()->json($search->run($q));
        }

        $box = $this->box($r);
        $folder = $this->folder($r);
        $filter = $r->query('filter', 'all');

        $pool = MailMessage::box($box)->where('folder', $folder)
            ->orderByDesc('sent_at')->orderByDesc('uid')->get();

        $tally = ['all' => $pool->count(), 'person' => 0, 'update' => 0, 'receipt' => 0];
        foreach ($pool as $m) {
            if (isset($tally[$m->kind])) {
                $tally[$m->kind]++;
            }
        }

        $items = $filter === 'all' ? $pool : $pool->where('kind', $filter);

        return response()->json([
            'box' => $box,
            'folder' => $folder,
            'unread' => $pool->where('seen', false)->count(),
            'tally' => $tally,
            'items' => $items->values()->map(fn ($m) => $this->row($m)),
        ]);
    }

    public function show(MailMessage $message)
    {
        return response()->json($this->row($message) + [
            'body' => $message->body_text,
            'html' => $message->body_html ? $this->safeHtml($message->body_html) : null,
            'reason' => $message->kind_reason,
            'files' => $message->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'size' => $a->size_label,
                'kind' => $this->fileKind($a->mime, $a->name),
                'ready' => $a->cached,
                'url' => route('admin.mail.file', $a),
            ])->values(),
        ]);
    }

    /**
     * Hands back one attachment.
     *
     * Only what we are confident about opens in the browser. Everything else —
     * archives, anything executable, anything unidentified — downloads,
     * because the browser is the wrong place to find out what is inside a
     * file. The filename in the header is the sanitised one, and the path
     * comes from our generated name, never the sender's.
     *
     * A file we are not currently holding is not a missing file. The original
     * is still in the mailbox, so we ask for it and it is here within the
     * minute — the web process cannot read the mail store itself.
     */
    public function file(MailAttachment $attachment)
    {
        if (! $attachment->cached) {
            $m = $attachment->message;

            MailAction::firstOrCreate([
                'mailbox' => $m->mailbox,
                'folder' => $m->folder,
                'uid' => $m->uid,
                'action' => 'fetch',
                'applied_at' => null,
            ], ['requested_by' => auth()->id()]);

            return response()->json([
                'fetching' => true,
                'message' => 'Getting that file off the server — try again in a minute.',
            ], 202);
        }

        return response()->file($attachment->path, [
            'Content-Type' => $attachment->previewable ? $attachment->mime : 'application/octet-stream',
            'Content-Disposition' => ($attachment->previewable ? 'inline' : 'attachment')
                .'; filename="'.str_replace('"', '', $attachment->name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Which glyph the room draws beside the file. */
    private function fileKind(?string $mime, string $name): string
    {
        $mime = strtolower((string) $mime);
        $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));

        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if ($mime === 'application/pdf' || $ext === 'pdf') {
            return 'pdf';
        }
        if (in_array($ext, ['zip', 'rar', '7z', 'gz', 'tar', 'dmg', 'exe', 'app', 'msi', 'pkg'], true)) {
            return 'locked';
        }
        if (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'pages', 'numbers'], true)) {
            return 'doc';
        }

        return 'file';
    }

    /**
     * The room writes an intent and moves on. The scheduler applies it to
     * Dovecot within the minute — the web SAPI cannot shell out, and that
     * restriction is the point rather than an obstacle.
     */
    public function act(Request $r)
    {
        $data = $r->validate([
            'id' => ['required', 'integer', 'exists:mail_messages,id'],
            'action' => ['required', 'string', 'in:'.implode(',', MailAction::ACTIONS)],
        ]);

        $m = MailMessage::findOrFail($data['id']);

        MailAction::create([
            'mailbox' => $m->mailbox,
            'folder' => $m->folder,
            'uid' => $m->uid,
            'action' => $data['action'],
            'requested_by' => auth()->id(),
        ]);

        // Show it immediately; the queue makes it true.
        match ($data['action']) {
            'seen' => $m->update(['seen' => true]),
            'unseen' => $m->update(['seen' => false]),
            'flag' => $m->update(['flagged' => true]),
            'unflag' => $m->update(['flagged' => false]),
            default => $m->delete(),
        };

        return response()->json(['ok' => true]);
    }

    private function row(MailMessage $m): array
    {
        return [
            'id' => $m->id,
            'box' => $m->mailbox,
            'folder' => $m->folder,
            'who' => $m->from_name,
            'addr' => $m->from_email,
            'subj' => $m->subject,
            'prev' => $m->preview,
            'when' => $m->when,
            'kind' => $m->kind,
            'seen' => (bool) $m->seen,
            'flagged' => (bool) $m->flagged,
            'attach' => (bool) $m->has_attachments,
        ];
    }

    private function box(Request $r): string
    {
        $box = (string) $r->query('box', 'media');

        abort_unless(array_key_exists($box, config('mailroom.boxes')), 404);

        return $box;
    }

    private function folder(Request $r): string
    {
        $folder = (string) $r->query('folder', 'INBOX');

        abort_unless(array_key_exists($folder, config('mailroom.folders')), 404);

        return $folder;
    }

    /**
     * HTML mail is somebody else's markup running inside our page. It renders
     * in a sandboxed frame, and before it gets there: scripts and frames go,
     * event handlers go, and every remote image becomes data-src so it only
     * loads when asked for — which is also what holds back the tracking pixel.
     */
    private function safeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed|form|link|meta)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<(script|iframe|object|embed|form|link|meta)\b[^>]*/?>#is', '', $html) ?? $html;
        $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#is', '', $html) ?? $html;
        $html = preg_replace('#(<[^>]+\s)(src|background)\s*=\s*(["\']?)(https?://)#i', '$1data-$2=$3$4', $html) ?? $html;
        $html = preg_replace('#javascript:#i', '', $html) ?? $html;

        return $html;
    }
}
