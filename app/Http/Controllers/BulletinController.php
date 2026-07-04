<?php
namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Bulletin;
use App\Models\Department;
use App\Models\BulletinLine;
use App\Models\Event;
use App\Models\Hymn;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BulletinController extends Controller
{
    /** Single homepage — renders published snapshot for guests, editable live rows for clerks. */
    public function home(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && in_array($user->role, ['clerk', 'super_admin'], true);
        $previewPublic = $isAdmin && $request->boolean('preview');
        $canEdit = $isAdmin && ! $previewPublic;
        // Week-mode preview — admins can append ?preview-week=1 to test the
        // guest-facing week-mode rendering.
        $weekModePreview = $isAdmin && $request->boolean('preview-week');

        $bulletinId = $request->integer('bulletin');

        if ($canEdit && $bulletinId) {
            $bulletin = Bulletin::find($bulletinId);
            if (! $bulletin) abort(404);
        } elseif ($canEdit) {
            // Admin default: the bulletin that is "active for now" (time-aware AM/PM picker)
            // so when clerk lands on the home page during an event evening, she sees that night.
            $bulletin = Bulletin::activeForNow() ?? Bulletin::orderByDesc('service_date')->first();
        } else {
            // Public: the bulletin that's live right now per activeForNow(), but only if it's published
            // AND its service_date is within the next 7 days (so early-published drafts don't leak).
            $bulletin = Bulletin::activeForNow();
            if ($bulletin && (! $bulletin->published_at || $bulletin->service_date->gt(now()->addDays(7)))) {
                $bulletin = Bulletin::whereNotNull('published_at')
                    ->where('service_date', '<=', now()->addDays(7))
                    ->orderByDesc('service_date')
                    ->first();
            }
        }

        if ($canEdit && $bulletin) {
            $bulletin->load(['lines', 'announcements']);
        }

        // Prev/next bulletin IDs for clerk navigation arrows
        $prevBulletinId = null;
        $nextBulletinId = null;
        if ($canEdit && $bulletin) {
            $prevBulletinId = Bulletin::where('service_date', '<', $bulletin->service_date)
                ->orderByDesc('service_date')->value('id');
            $nextBulletinId = Bulletin::where('service_date', '>', $bulletin->service_date)
                ->orderBy('service_date')->value('id');
        }

        $upcomingEventsQ = Event::where('is_public', true)
            ->where('start_at', '>=', now()->startOfDay())
            ->with('department');
        if (! $user) {
            // Guests only see general events (no department_id). Dept-specific stuff
            // (Ushers meetings, Comm schedule, etc.) stays behind login.
            $upcomingEventsQ->whereNull('department_id');
        }
        $upcomingEvents = $upcomingEventsQ->orderBy('start_at')->limit(8)->get();

        $departments = Department::orderBy('name')->get();

        // Duty calendar = every event tied to a department, next 60 days
        $dutyEvents = Event::whereNotNull('department_id')
            ->where('start_at', '>=', now()->startOfDay())
            ->where('start_at', '<=', now()->addDays(60))
            ->with('department')
            ->orderBy('start_at')
            ->get();

        // Auto-detect week-mode: if the active bulletin is a regular Sabbath
        // service AND today is NOT the service day AND we are NOT inside any
        // event window AND the manual override flag is off — hide the order
        // of service from guests, leaving only announcements + Sabbath reminder.
        $weekModeAuto = false;
        if ($bulletin && ! $canEdit) {
            $today = now()->setTimezone('America/New_York')->startOfDay();
            $serviceDay = $bulletin->service_date->copy()->setTimezone('America/New_York')->startOfDay();
            $isServiceDay = $today->equalTo($serviceDay);

            // Event window override: today inside [service_date .. event_ends_at]
            // OR bulletin is explicitly an 'event' kind
            $hasEventWindow = false;
            if (($bulletin->kind ?? null) === 'event') {
                $hasEventWindow = true;
            } elseif ($bulletin->event_ends_at) {
                $eventEnd = $bulletin->event_ends_at->copy()->setTimezone('America/New_York')->endOfDay();
                $hasEventWindow = $today->between($serviceDay, $eventEnd);
            }

            // Manual override: always_show_during_week column
            $alwaysShow = (bool) ($bulletin->always_show_during_week ?? false);

            $weekModeAuto = ! ($isServiceDay || $hasEventWindow || $alwaysShow);
        }

        $weekMode = $weekModePreview || $weekModeAuto;

        return view('welcome', [
            'bulletin'        => $bulletin,
            'canEdit'         => $canEdit,
            'previewPublic'   => $previewPublic,
            'weekMode'        => $weekMode,
            'prevBulletinId'  => $prevBulletinId,
            'nextBulletinId'  => $nextBulletinId,
            'upcomingEvents'  => $upcomingEvents,
            'departments'     => $departments,
            'dutyEvents'      => $dutyEvents,
        ]);
    }

    /** Standalone bulletin view (for direct link / print). */
    public function show(Bulletin $bulletin)
    {
        // Public link must use published snapshot; if no snapshot yet, 404 for guests
        if (! $bulletin->published_snapshot && ! optional(request()->user())?->role) {
            abort(404);
        }
        return view('bulletins.show', compact('bulletin'));
    }

    /** PATCH /bulletins/{bulletin} — update title/service_date. */
    public function updateMeta(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'title'        => 'sometimes|string|max:180',
            'service_date' => 'sometimes|date',
            'theme'        => 'sometimes|string|in:default,communion,easter,christmas,mothers,thanksgiving',
            'always_show_during_week' => 'sometimes|boolean',
        ]);
        $bulletin->update($data);
        return response()->json(['ok' => true, 'bulletin' => $bulletin->only(['id', 'title', 'service_date', 'theme'])]);
    }

    /** POST /bulletins/{bulletin}/publish — snapshot + flip is_published.
     *  Single source of truth: BulletinPublisher service. Audit-logged. */
    public function publish(Bulletin $bulletin, \App\Services\BulletinPublisher $publisher, \Illuminate\Http\Request $request)
    {
        // PUBLISH_REDIRECT_FALLBACK — supports both XHR and plain HTML form submission.
        // If JS is broken on mobile, the Go Live button does a real POST; we redirect back with a flash.
        $bulletin = $publisher->publish($bulletin, $request->user());
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'is_published' => $bulletin->is_published,
                'published_at' => $bulletin->published_at?->toIso8601String(),
                'has_previous'=> $bulletin->hasAvailablePreviousVersion(),
            ]);
        }
        return redirect()->to(url()->previous() ?: route('welcome'))->with('status', 'Published — live now.');
    }

    /**
     * GET /bulletins/{bulletin}/pdf — download the bulletin as a PDF.
     * Default: current published snapshot. Pass ?version=previous for the prior
     * published version (available for 8 hours after supersession).
     * If never published, streams the current draft.
     */
    public function downloadPdf(Request $request, Bulletin $bulletin): Response
    {
        $version = $request->query('version', 'current');
        $user    = $request->user();
        $isClerk = $user && in_array($user->role, ['clerk', 'super_admin'], true);

        if ($version === 'previous') {
            if (! $bulletin->hasAvailablePreviousVersion()) abort(404, 'No previous version available.');
            $snapshot            = $bulletin->previous_published_snapshot;
            $previousPublishedAt = $bulletin->previous_published_at->format('M j · g:ia');
            $isPrevious          = true;
        } elseif ($isClerk) {
            // Clerks always get the LIVE draft — PDF reflects their latest edits even if not published
            $snapshot            = $bulletin->snapshotCurrentState();
            $previousPublishedAt = null;
            $isPrevious          = false;
        } elseif ($bulletin->published_snapshot) {
            // Public users get the published snapshot
            $snapshot            = $bulletin->published_snapshot;
            $previousPublishedAt = null;
            $isPrevious          = false;
        } else {
            // Never published — use live draft (also fine for the rare guest who lands here)
            $snapshot            = $bulletin->snapshotCurrentState();
            $previousPublishedAt = null;
            $isPrevious          = false;
        }

        $date = !empty($snapshot['service_date'])
            ? \Carbon\Carbon::parse($snapshot['service_date'])->format('Y-m-d')
            : $bulletin->service_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $label    = $isPrevious ? '-previous' : '';
        $filename = "bulletin-{$date}{$label}.pdf";

        // ?layout=2up → one landscape sheet, bulletin printed twice for a cut-in-half
        // double-sided print (2 identical bulletins per sheet). Default stays portrait.
        $twoUp = $request->query('layout') === '2up';
        $response = Pdf::loadView($twoUp ? 'bulletins.pdf-2up' : 'bulletins.pdf', [
                'snapshot'            => $snapshot,
                'isPrevious'          => $isPrevious,
                'previousPublishedAt' => $previousPublishedAt,
            ])
            ->setPaper('letter', $twoUp ? 'landscape' : 'portrait')
            ->download(($twoUp ? '2up-' : '') . $filename);

        // Prevent shared/CDN cache poisoning: the PDF content depends on whether the requester
        // is a clerk (live draft) or a guest (published snapshot). A shared cache that stored
        // a clerk-served draft and replayed it to a guest would leak unpublished content.
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        return $response;
    }

    /** POST /bulletins/{bulletin}/lines — append a line. */
    public function storeLine(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'section' => 'nullable|string|max:180',
            'part'    => 'nullable|string|max:120',
            'person'  => 'nullable|string|max:180',
            'kind'    => 'nullable|in:line,section_header',
            'after_id' => 'nullable|integer',
        ]);
        $maxOrder = $bulletin->lines()->max('sort_order') ?? -1;
        $line = $bulletin->lines()->create([
            'section'    => $data['section'] ?? null,
            'part'       => $data['part'] ?? null,
            'person'     => $data['person'] ?? null,
            'kind'       => $data['kind'] ?? 'line',
            'sort_order' => $maxOrder + 1,
        ]);
        return response()->json(['ok' => true, 'line' => $line]);
    }

    /** PATCH /bulletins/{bulletin}/lines/{line} — update an individual field on a line. */
    public function updateLine(Request $request, Bulletin $bulletin, BulletinLine $line): JsonResponse
    {
        abort_unless($line->bulletin_id === $bulletin->id, 404);
        $data = $request->validate([
            'section'    => 'sometimes|nullable|string|max:180',
            'part'       => 'sometimes|nullable|string|max:120',
            'person'     => 'sometimes|nullable|string|max:180',
            'sort_order' => 'sometimes|integer',
        ]);
        $line->update($data);
        return response()->json(['ok' => true, 'line' => $line]);
    }

    /** DELETE a line. */
    public function destroyLine(Bulletin $bulletin, BulletinLine $line): JsonResponse
    {
        abort_unless($line->bulletin_id === $bulletin->id, 404);
        $line->delete();
        return response()->json(['ok' => true]);
    }

    /** PATCH /bulletins/{bulletin}/lines/reorder — body { ids: [12,14,13,...] } in new order. */
    public function reorderLines(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);
        $valid = $bulletin->lines()->pluck('id')->all();
        $sortOrder = 0;
        DB::transaction(function () use ($data, $valid, &$sortOrder) {
            foreach ($data['ids'] as $id) {
                if (! in_array($id, $valid, true)) continue;
                BulletinLine::where('id', $id)->update(['sort_order' => $sortOrder++]);
            }
        });
        return response()->json(['ok' => true]);
    }

    /** POST /bulletins/{bulletin}/lines/restore — body { lines: [{section,part,person,kind}, ...] }. Replaces all lines. Used by client undo after a destructive op (reset, mass delete). */
    public function restoreLines(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'lines'   => 'required|array',
            'lines.*.section' => 'nullable|string|max:180',
            'lines.*.part'    => 'nullable|string|max:120',
            'lines.*.person'  => 'nullable|string|max:180',
            'lines.*.kind'    => 'nullable|in:line,section_header',
        ]);
        DB::transaction(function () use ($data, $bulletin) {
            $bulletin->lines()->delete();
            foreach ($data['lines'] as $i => $row) {
                $bulletin->lines()->create([
                    'section'    => $row['section'] ?? null,
                    'part'       => $row['part'] ?? null,
                    'person'     => $row['person'] ?? null,
                    'kind'       => $row['kind'] ?? 'line',
                    'sort_order' => $i,
                ]);
            }
        });
        return response()->json(['ok' => true]);
    }

    /** Announcement CRUD — mirror of line CRUD. */
    public function storeAnnouncement(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'title'  => 'nullable|string|max:180',
            'detail' => 'nullable|string|max:1000',
        ]);
        $maxOrder = $bulletin->announcements()->max('sort_order') ?? -1;
        $a = $bulletin->announcements()->create([
            'title'      => $data['title'] ?? '',
            'detail'     => $data['detail'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);
        return response()->json(['ok' => true, 'announcement' => $a]);
    }

    public function updateAnnouncement(Request $request, Bulletin $bulletin, Announcement $announcement): JsonResponse
    {
        abort_unless($announcement->bulletin_id === $bulletin->id, 404);
        $data = $request->validate([
            'title'  => 'sometimes|string|max:180',
            'detail' => 'sometimes|nullable|string|max:1000',
            'video_url' => 'sometimes|nullable|string|max:500',
        ]);
        $announcement->update($data);
        return response()->json(['ok' => true, 'announcement' => $announcement]);
    }

    public function destroyAnnouncement(Bulletin $bulletin, Announcement $announcement): JsonResponse
    {
        abort_unless($announcement->bulletin_id === $bulletin->id, 404);
        $announcement->delete();
        return response()->json(['ok' => true]);
    }

    /** POST /bulletins/{bulletin}/announcements/{announcement}/image — attach a compressed image. */
    public function uploadAnnouncementImage(Request $request, Bulletin $bulletin, Announcement $announcement): JsonResponse
    {
        abort_unless($announcement->bulletin_id === $bulletin->id, 404);
        $request->validate(['image' => ['required', 'file', 'extensions:jpg,jpeg,png,webp,gif', 'max:10240']]);
        $dir = public_path('announcements');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        if ($announcement->image_path) { $old = public_path($announcement->image_path); if (is_file($old)) @unlink($old); }
        $name = 'ann-' . $announcement->id . '-' . now()->format('YmdHis') . '-' . \Illuminate\Support\Str::random(5) . '.jpg';
        if (! $this->compressAnnImage($request->file('image')->getRealPath(), $dir . '/' . $name, 1400, 82)) {
            return response()->json(['ok' => false, 'error' => 'Could not process image'], 422);
        }
        $announcement->update(['image_path' => 'announcements/' . $name]);
        return response()->json(['ok' => true, 'image_url' => '/announcements/' . $name]);
    }

    /** DELETE the announcement image. */
    public function removeAnnouncementImage(Bulletin $bulletin, Announcement $announcement): JsonResponse
    {
        abort_unless($announcement->bulletin_id === $bulletin->id, 404);
        if ($announcement->image_path) {
            $abs = public_path($announcement->image_path);
            if (is_file($abs)) @unlink($abs);
            $announcement->update(['image_path' => null]);
        }
        return response()->json(['ok' => true]);
    }

    private function compressAnnImage(string $src, string $dest, int $maxW, int $q): bool
    {
        $info = @getimagesize($src);
        if (! $info) return false;
        [$w, $h, $type] = $info;
        $im = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => @imagecreatefromwebp($src),
            IMAGETYPE_GIF  => @imagecreatefromgif($src),
            default => null,
        };
        if (! $im) return false;
        if ($w > $maxW) {
            $nh = (int) ($h * $maxW / $w);
            $r = imagecreatetruecolor($maxW, $nh);
            imagecopyresampled($r, $im, 0, 0, 0, 0, $maxW, $nh, $w, $h);
            imagedestroy($im); $im = $r;
        }
        $ok = imagejpeg($im, $dest, $q);
        imagedestroy($im);
        return (bool) $ok;
    }


    /**
     * GET /api/suggestions?q=Pas&scope=person|hymn|bible|any
     * Returns {people, hymns, books} — only the relevant array(s) are populated based on scope.
     *
     * Scope rules (Karlon's canonical autocomplete rules):
     *   - "song" or "hymn" mentioned in line context → scope=hymn → returns hymns (numbers + titles)
     *   - "scripture" mentioned in line context     → scope=bible → returns Bible book names (e.g. "John")
     *   - everything else                           → scope=person → returns people from past bulletins
     *   - "" or "any"                               → returns all three (used by free-form fields)
     */
    public function suggestions(Request $request): JsonResponse
    {
        // SUGGESTIONS_AUDIT_LOG — log every call for ~24h so we can see if Andre's browser is reaching this endpoint.
        // Remove the AuditLog::record call after Andre has confirmed he can edit a bulletin.
        try {
            \App\Models\AuditLog::record(
                event: 'suggestions_called',
                userId: auth()->id(),
                description: 'q=' . substr((string) $request->query('q', ''), 0, 40) . ' scope=' . $request->query('scope', '') . ' user=' . (auth()->user()->email ?? 'guest'),
            );
        } catch (\Throwable $e) {}
        $q = trim((string) $request->query('q', ''));
        $scope = $request->query('scope', '');
        if ($scope === 'any') $scope = '';
        if (strlen($q) < 1) return response()->json(['people' => [], 'hymns' => [], 'books' => []]);

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

        $people = [];
        if ($scope === '' || $scope === 'person') {
            $people = DB::table('bulletin_lines')
                ->leftJoin('person_blocklist', 'bulletin_lines.person', '=', 'person_blocklist.person')
                ->select('bulletin_lines.person')
                ->whereNotNull('bulletin_lines.person')
                ->where('bulletin_lines.person', 'like', $like)
                ->whereNull('person_blocklist.person')
                ->distinct()
                ->orderBy('bulletin_lines.person')
                ->limit(8)
                ->pluck('person');
        }

        $hymns = collect();
        if ($scope === '' || $scope === 'hymn') {
            $hymnsQuery = Hymn::query()->orderBy('number');
            if (is_numeric($q)) {
                $hymnsQuery->where('number', 'like', $q . '%');
            } else {
                $hymnsQuery->where('title', 'like', $like);
            }
            $hymns = $hymnsQuery->limit(10)->get(['number', 'title'])->map(fn($h) => [
                'label' => sprintf('#%d %s', $h->number, $h->title),
                'number' => $h->number,
                'title'  => $h->title,
            ]);
        }

        $books = [];
        if ($scope === '' || $scope === 'bible') {
            $books = $this->matchBibleBooks($q);
        }

        return response()->json([
            'people' => $people,
            'hymns'  => $hymns,
            'books'  => $books,
        ]);
    }

    /**
     * Static list of the 66 Protestant canonical Bible books in order.
     * Used by the suggestions endpoint when scope=bible. The bulletin only
     * stores REFERENCES (e.g. "John 3:16") — the user picks the book name from
     * autocomplete, then types the chapter:verse manually.
     */
    private function matchBibleBooks(string $q): array
    {
        static $books = [
            // Old Testament
            'Genesis','Exodus','Leviticus','Numbers','Deuteronomy',
            'Joshua','Judges','Ruth','1 Samuel','2 Samuel','1 Kings','2 Kings',
            '1 Chronicles','2 Chronicles','Ezra','Nehemiah','Esther','Job',
            'Psalms','Proverbs','Ecclesiastes','Song of Solomon','Isaiah','Jeremiah',
            'Lamentations','Ezekiel','Daniel','Hosea','Joel','Amos','Obadiah',
            'Jonah','Micah','Nahum','Habakkuk','Zephaniah','Haggai','Zechariah','Malachi',
            // New Testament
            'Matthew','Mark','Luke','John','Acts','Romans',
            '1 Corinthians','2 Corinthians','Galatians','Ephesians','Philippians','Colossians',
            '1 Thessalonians','2 Thessalonians','1 Timothy','2 Timothy','Titus','Philemon',
            'Hebrews','James','1 Peter','2 Peter','1 John','2 John','3 John','Jude','Revelation',
        ];
        $needle = mb_strtolower($q);
        $matches = [];
        foreach ($books as $book) {
            if (str_contains(mb_strtolower($book), $needle)) {
                $matches[] = ['label' => $book, 'name' => $book];
                if (count($matches) >= 10) break;
            }
        }
        return $matches;
    }

    /** GET /people — list distinct people seen in any bulletin line, sorted. Clerk-only management view. */
    public function listPeople(): JsonResponse
    {
        // Names from past bulletin lines
        $fromLines = DB::table('bulletin_lines')
            ->select('person')
            ->whereNotNull('person')
            ->where('person', '!=', '')
            ->distinct()
            ->pluck('person');

        // Plus names manually added via /admin/names + button (no bulletin yet)
        $fromManaged = DB::table('people')->pluck('name');

        $names = $fromLines->merge($fromManaged)->unique()->sort()->values();
        $blocked = DB::table('person_blocklist')->pluck('person');

        return response()->json([
            'all'     => $names,
            'blocked' => $blocked,
        ]);
    }

    /** POST /api/people — add a name manually so it appears in autocomplete before being used. */
    public function addPerson(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
        ]);
        $name = trim($data['name']);
        DB::table('people')->updateOrInsert(['name' => $name], ['updated_at' => now(), 'created_at' => now()]);
        // If it was previously blocked, unblock so it shows in autocomplete
        DB::table('person_blocklist')->where('person', $name)->delete();
        return response()->json(['ok' => true, 'name' => $name]);
    }

    /** PATCH /api/people — rename a person across managed list AND every bulletin line. */
    public function renamePerson(Request $request): JsonResponse
    {
        $data = $request->validate([
            'old' => 'required|string|max:180',
            'new' => 'required|string|max:180|different:old',
        ]);
        $old = trim($data['old']);
        $new = trim($data['new']);

        DB::transaction(function () use ($old, $new) {
            // Update managed list
            DB::table('people')->where('name', $old)->update(['name' => $new, 'updated_at' => now()]);
            // Make sure the new name exists in managed list
            DB::table('people')->updateOrInsert(['name' => $new], ['updated_at' => now(), 'created_at' => now()]);
            // Update every bulletin line that uses the old name
            DB::table('bulletin_lines')->where('person', $old)->update(['person' => $new]);
            // If old was blocked, transfer block to new
            $wasBlocked = DB::table('person_blocklist')->where('person', $old)->exists();
            DB::table('person_blocklist')->where('person', $old)->delete();
            if ($wasBlocked) {
                DB::table('person_blocklist')->updateOrInsert(['person' => $new], ['updated_at' => now()]);
            }
        });

        return response()->json(['ok' => true, 'old' => $old, 'new' => $new]);
    }

    /** POST /people/block — block a name from autocomplete (and optionally clear it from past lines). */
    public function blockPerson(Request $request): JsonResponse
    {
        $data = $request->validate([
            'person' => 'required|string|max:180',
            'clear_from_lines' => 'sometimes|boolean',
        ]);
        DB::table('person_blocklist')->updateOrInsert(['person' => $data['person']], ['updated_at' => now()]);
        if (! empty($data['clear_from_lines'])) {
            DB::table('bulletin_lines')->where('person', $data['person'])->update(['person' => null]);
        }
        return response()->json(['ok' => true]);
    }

    /** POST /people/unblock — restore a name to autocomplete. */
    public function unblockPerson(Request $request): JsonResponse
    {
        $data = $request->validate(['person' => 'required|string|max:180']);
        DB::table('person_blocklist')->where('person', $data['person'])->delete();
        return response()->json(['ok' => true]);
    }
// Additional BulletinController methods — to be appended inside the class.
// This is the block to splice in before the class closing brace.

    /** POST /bulletins/next — create next week's bulletin, clone order structure with blank person fields. */
    public function startNextWeek(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $current = \App\Models\Bulletin::orderByDesc('service_date')->first();
        $nextDate = $current
            ? $current->service_date->copy()->addDays(7)
            : now()->next('Saturday');

        $next = \App\Models\Bulletin::create([
            'title' => 'Sabbath Service',
            'service_date' => $nextDate,
            'author_id' => $request->user()->id,
            'is_published' => false,
        ]);

        if ($current) {
            // NEXTWEEK_ANNOUNCEMENTS_CLONED — carry over lines (people blank) AND announcements (verbatim).
            $sort = 0;
            foreach ($current->lines()->orderBy('sort_order')->get() as $line) {
                $next->lines()->create([
                    'section' => $line->section,
                    'part'    => $line->part,
                    'person'  => null, // blank out person each new week
                    'kind'    => $line->kind,
                    'sort_order' => $sort++,
                ]);
            }
            // Announcements carry over verbatim — admin deletes the ones that no longer apply.
            $aSort = 0;
            foreach ($current->announcements()->orderBy('sort_order')->get() as $a) {
                $next->announcements()->create([
                    'title'      => $a->title,
                    'detail'     => $a->detail,
                    'sort_order' => $aSort++,
                ]);
            }
        }

        return response()->json(['ok' => true, 'bulletin_id' => $next->id, 'redirect' => url('/')]);
    }

    /** GET /api/bulletins/list — list of bulletins for the thumbnail picker (admin only).
     *  Note: route already lives in the role:clerk middleware group. */
    public function listBulletins(): JsonResponse
    {
        $bulletins = Bulletin::orderByDesc('service_date')
            ->orderByRaw("FIELD(service_time, 'evening', 'morning')")
            ->limit(40)
            ->get(['id', 'title', 'service_date', 'service_time', 'kind', 'event_name', 'event_night_number', 'event_total_nights', 'published_at']);
        return response()->json(['bulletins' => $bulletins]);
    }

    /** POST /bulletins/event-series — creates all nights of a multi-night event in one shot. */
    public function createEventSeries(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'event_name'           => 'required|string|max:120',
            'first_date'           => 'required|date',
            'last_date'            => 'required|date|after_or_equal:first_date',
            'skip_sabbath_mornings'=> 'sometimes|boolean',
        ]);
        $skipSabbathMornings = (bool)($data['skip_sabbath_mornings'] ?? true);

        // Enumerate every day between first_date and last_date; build the list of
        // (date, service_time) bulletins to create. Evenings for every day; Sabbath
        // mornings only if the user didn't opt out.
        $start = \Carbon\Carbon::parse($data['first_date']);
        $end   = \Carbon\Carbon::parse($data['last_date']);
        $plan  = []; // [[date, service_time], ...]

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $isSaturday = $d->dayOfWeek === \Carbon\Carbon::SATURDAY;
            // Sabbath morning: include only if user did NOT check "Skip Sabbath mornings".
            // Even then it's a REGULAR 'sabbath' bulletin, not an event night.
            if ($isSaturday && ! $skipSabbathMornings) {
                $plan[] = ['date' => $d->toDateString(), 'service_time' => 'morning', 'is_event' => false];
            }
            // Evening service = event night (every day).
            $plan[] = ['date' => $d->toDateString(), 'service_time' => 'evening', 'is_event' => true];
        }

        $totalNights = count(array_filter($plan, fn($p) => $p['is_event']));

        $created = [];
        \DB::transaction(function () use ($plan, $data, $request, $totalNights, $end, &$created) {
            $nightNumber = 0;
            foreach ($plan as $slot) {
                // Skip if a bulletin already exists on this (date, service_time) — avoids unique-key clash
                $exists = Bulletin::where('service_date', $slot['date'])
                    ->where('service_time', $slot['service_time'])
                    ->exists();
                if ($exists) continue;

                if ($slot['is_event']) {
                    $nightNumber++;
                    $created[] = Bulletin::create([
                        'title'              => $data['event_name'] . ' · Night ' . $nightNumber,
                        'service_date'       => $slot['date'],
                        'service_time'       => $slot['service_time'],
                        'kind'               => 'event_night',
                        'event_name'         => $data['event_name'],
                        'event_night_number' => $nightNumber,
                        'event_total_nights' => $totalNights,
                        'event_ends_at'      => $end->toDateString(),
                        'author_id'          => $request->user()->id,
                        'is_published'       => false,
                    ]);
                } else {
                    // Regular Sabbath morning inside the event window
                    $created[] = Bulletin::create([
                        'title'        => 'Sabbath Service',
                        'service_date' => $slot['date'],
                        'service_time' => 'morning',
                        'kind'         => 'sabbath',
                        'author_id'    => $request->user()->id,
                        'is_published' => false,
                    ]);
                }
            }
        });

        $firstCreated = collect($created)->first();
        return response()->json([
            'ok'            => true,
            'count'         => count($created),
            'total_nights'  => $totalNights,
            'bulletin_id'   => $firstCreated?->id,
            'redirect'      => $firstCreated ? url('/?bulletin=' . $firstCreated->id) : url('/'),
        ]);
    }

    /**
     * POST /bulletins/{bulletin}/load-standard-order — prefill blank bulletin
     * with default SDA order of service.
     *
     * SECTION_OF_TRUTH (2026-05-23, option B): `section` is populated ONLY on
     * kind=section_header rows. Line rows leave `section` NULL. The header's
     * position in sort_order determines which lines visually belong to it —
     * no per-line section attribute, no phantom headers, WYSIWYG.
     */
    public function loadStandardOrder(\App\Models\Bulletin $bulletin): \Illuminate\Http\JsonResponse
    {
        $bulletin->lines()->delete();

        $pastoral = 'Pastoral Greetings / Baby Dedication';
        $order = [
            ['section' => null,      'part' => 'Call to Worship',     'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Response Song',       'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Invocation',          'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Announcements',       'person' => null, 'kind' => 'line'],
            ['section' => $pastoral, 'part' => null,                  'person' => null, 'kind' => 'section_header'],
            ['section' => null,      'part' => 'Welcome',             'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Opening Hymn',        'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Scripture Reading',   'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Intercessory Prayer', 'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => "Children's Story",    'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Special Music',       'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Sermon',              'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Closing Hymn',        'person' => null, 'kind' => 'line'],
            ['section' => null,      'part' => 'Benediction',         'person' => null, 'kind' => 'line'],
        ];
        foreach ($order as $i => $row) {
            $bulletin->lines()->create($row + ['sort_order' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /bulletins/{bulletin} — soft-delete the bulletin (super_admin only).
     * Lines + announcements stay attached but inaccessible while parent is trashed.
     * Returns the deleted ID so the client can offer a 30-second undo.
     */
    public function destroy(Request $request, Bulletin $bulletin): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'super_admin') {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $serviceDate = optional($bulletin->service_date)->toDateString();
        $bulletin->delete(); // soft delete

        \App\Models\AuditLog::record(
            event: 'bulletin_deleted',
            userId: $user->id,
            description: $user->name . " deleted bulletin id={$bulletin->id} (service_date={$serviceDate})",
            meta: ['bulletin_id' => $bulletin->id, 'service_date' => $serviceDate],
        );

        return response()->json([
            'ok' => true,
            'bulletin_id' => $bulletin->id,
            'service_date' => $serviceDate,
        ]);
    }

    /**
     * POST /bulletins/{bulletinId}/restore — undelete a soft-deleted bulletin (undo).
     */
    public function restoreBulletin(Request $request, int $bulletinId): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user || $user->role !== 'super_admin') {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $bulletin = Bulletin::withTrashed()->findOrFail($bulletinId);
        $bulletin->restore();

        \App\Models\AuditLog::record(
            event: 'bulletin_restored',
            userId: $user->id,
            description: $user->name . " restored bulletin id={$bulletinId}",
            meta: ['bulletin_id' => $bulletinId],
        );

        return response()->json(['ok' => true, 'bulletin_id' => $bulletinId]);
    }
}
