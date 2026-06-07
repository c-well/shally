<?php

use App\Http\Controllers\BulletinController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Apex / homepage = landing (hero slider + marketing). NOT the bulletin.
// Bulletin moved to /welcome (admin-edit + public-view via the same controller).
Route::get('/', fn () => view('landing', [
    'heroPage'    => App\Models\Page::bySlug('landing'),
    'sliderIntro' => App\Models\Page::bySlug('slider-intro'),
    'slides'      => App\Models\Slide::active()->get(),
]))->name('home');
Route::get('/welcome', [BulletinController::class, 'home'])->name('welcome');
Route::get('/bulletin/{bulletin}', [BulletinController::class, 'show'])->name('bulletins.show');
Route::get('/bulletin/{bulletin}/pdf', [BulletinController::class, 'downloadPdf'])->name('bulletins.pdf');
Route::get('/lesson',          [\App\Http\Controllers\LessonController::class, 'show'])->name('lesson.show');
Route::get('/lesson/{lesson}', [\App\Http\Controllers\LessonController::class, 'show'])->whereNumber('lesson')->name('lesson.week');

// Public pages — content editable from /admin/pages
Route::get ('/peace-notes',         [\App\Http\Controllers\SermonController::class, 'index'])->name('peace-notes');
Route::get ('/peace-notes/{slug}',  [\App\Http\Controllers\SermonController::class, 'show'])
     ->where('slug', '[A-Za-z0-9-]+')->name('peace-notes.show');
Route::get ('/about',       fn () => view('about',       ['page' => \App\Models\Page::bySlug('about')]))->name('about');
Route::get ('/beliefs',     fn () => view('beliefs',     ['page' => \App\Models\Page::bySlug('beliefs')]))->name('beliefs');
Route::get ('/visit',       fn () => view('visit',       ['page' => \App\Models\Page::bySlug('visit')]))->name('visit');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/bible',   'bible')->name('bible');
Route::view('/hymnal',  'hymnal')->name('hymnal');
Route::middleware('seeker:optional')->group(function () {
    Route::get('/find-peace',                   [\App\Http\Controllers\FindPeaceController::class, 'index'])->name('find-peace.index');
    Route::get('/find-peace/saved',             [\App\Http\Controllers\FindPeaceController::class, 'saved'])->middleware('seeker:required')->name('find-peace.saved');
    Route::get('/find-peace/topic/{slug}',      [\App\Http\Controllers\FindPeaceController::class, 'topic'])->name('find-peace.topic')->where('slug', '[A-Za-z0-9-]+');
    Route::get ('/find-peace/share',            [\App\Http\Controllers\PeaceShareController::class, 'show'])->middleware('seeker:required')->name('peace.share.show');
    Route::post('/find-peace/share',            [\App\Http\Controllers\PeaceShareController::class, 'store'])->middleware('seeker:required')->name('peace.share.submit');
    Route::get('/find-peace/{slug}',            [\App\Http\Controllers\FindPeaceController::class, 'show'])->name('find-peace.show')->where('slug', '[A-Za-z0-9-]+');
});
Route::post('/peace/saved/{qa}', [\App\Http\Controllers\FindPeaceController::class, 'savedToggle'])->middleware(['seeker:required'])->where('qa', '[0-9]+')->name('peace.saved.toggle');
Route::post('/find-peace/poll', [\App\Http\Controllers\PeacePollController::class, 'submit'])->middleware('throttle:30,1')->name('find-peace.poll.submit');

// Peace seeker accounts — magic-link, passwordless, separate guard from admin.
Route::post('/peace/auth/request',         [\App\Http\Controllers\PeaceAuthController::class, 'request'])
     ->middleware(['throttle:10,60', \App\Http\Middleware\BlockAbusiveIps::class])
     ->name('peace.auth.request');
Route::get ('/peace/auth/verify/{token}',  [\App\Http\Controllers\PeaceAuthController::class, 'verify'])
     ->where('token', '[a-f0-9]{64}')
     ->name('peace.auth.verify');
Route::post('/peace/auth/logout',          [\App\Http\Controllers\PeaceAuthController::class, 'logout'])
     ->name('peace.auth.logout');

// Public, signed-token review actions (no login — token is the secret).
// Routes match Saturday-3pm cron emails.
Route::get('/peace/review/{token}/approve',        [\App\Http\Controllers\PeaceReviewController::class, 'approve'])->where('token','[a-f0-9]{64}')->name('peace.review.approve');
Route::get('/peace/review/{token}/discard',        [\App\Http\Controllers\PeaceReviewController::class, 'discard'])->where('token','[a-f0-9]{64}')->name('peace.review.discard');
Route::get('/peace/review/{token}/restore',        [\App\Http\Controllers\PeaceReviewController::class, 'restore'])->where('token','[a-f0-9]{64}')->name('peace.review.restore');
Route::get('/peace/review/{token}/confirm-delete', [\App\Http\Controllers\PeaceReviewController::class, 'confirmDelete'])->where('token','[a-f0-9]{64}')->name('peace.review.confirm-delete');
Route::get ('/search',  [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get ('/contact',  [\App\Http\Controllers\ContactController::class, 'show'])->name('contact.show');
Route::post('/contact',  [\App\Http\Controllers\ContactController::class, 'send'])
     ->middleware(['throttle:5,60', 'honeypot'])->name('contact.send');
Route::get ('/prayer',   [\App\Http\Controllers\PrayerController::class, 'show'])->name('prayer.show');
Route::post('/prayer',   [\App\Http\Controllers\PrayerController::class, 'send'])
     ->middleware(['throttle:3,60', 'honeypot'])->name('prayer.send');

// SEO infrastructure — full site indexing
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt',  [\App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

// Native event tracking — accepts batched interaction events from the in-page tracker.
// Public (no auth), rate-limited per session_id, CSRF-exempt for cross-context
// fetch+sendBeacon compatibility. Bot + DNT filtered in the controller.
Route::post('/api/events', [\App\Http\Controllers\EventIngestController::class, 'ingest'])
     ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
     ->middleware('throttle:60,1')
     ->name('events.ingest');

Route::middleware(['auth', 'role:clerk'])->group(function () {
    // Bulletin meta + lines + announcements + publish
    Route::patch('/bulletins/{bulletin}',           [BulletinController::class, 'updateMeta'])->name('bulletins.update');
    Route::delete('/bulletins/{bulletin}',          [BulletinController::class, 'destroy'])->name('bulletins.destroy');
    Route::post ('/bulletins/{bulletinId}/restore', [BulletinController::class, 'restoreBulletin'])->where('bulletinId', '[0-9]+')->name('bulletins.restore');
    Route::post ('/bulletins/{bulletin}/publish',   [BulletinController::class, 'publish'])->name('bulletins.publish');
    Route::post ('/bulletins/next-week',            [BulletinController::class, 'startNextWeek'])->name('bulletins.next-week');
    Route::post ('/bulletins/event-series',         [BulletinController::class, 'createEventSeries'])->name('bulletins.event-series');
    Route::get  ('/api/bulletins/list',             [BulletinController::class, 'listBulletins'])->name('bulletins.list');
    Route::post ('/bulletins/{bulletin}/load-standard-order', [BulletinController::class, 'loadStandardOrder'])->name('bulletins.load-standard');

    Route::post  ('/bulletins/{bulletin}/lines',      [BulletinController::class, 'storeLine'])->name('bulletins.lines.store');
    Route::patch ('/bulletins/{bulletin}/lines/reorder', [BulletinController::class, 'reorderLines'])->name('bulletins.lines.reorder');
    Route::post  ('/bulletins/{bulletin}/lines/restore', [BulletinController::class, 'restoreLines'])->name('bulletins.lines.restore');
    Route::patch ('/bulletins/{bulletin}/lines/{line}',  [BulletinController::class, 'updateLine'])->name('bulletins.lines.update');
    Route::delete('/bulletins/{bulletin}/lines/{line}',  [BulletinController::class, 'destroyLine'])->name('bulletins.lines.destroy');

    Route::post  ('/bulletins/{bulletin}/announcements', [BulletinController::class, 'storeAnnouncement'])->name('bulletins.announcements.store');
    Route::patch ('/bulletins/{bulletin}/announcements/{announcement}', [BulletinController::class, 'updateAnnouncement'])->name('bulletins.announcements.update');
    Route::delete('/bulletins/{bulletin}/announcements/{announcement}', [BulletinController::class, 'destroyAnnouncement'])->name('bulletins.announcements.destroy');

    // Event editing (inline on home page)
    Route::post  ('/events',              [EventController::class, 'store'])->name('events.store');
    Route::patch ('/events/{event}',      [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}',      [EventController::class, 'destroy'])->name('events.destroy');
    Route::post  ('/events/{eventId}/restore', [EventController::class, 'restoreEvent'])->whereNumber('eventId')->name('events.restore');
    Route::post  ('/events/{event}/flyer',[EventController::class, 'uploadFlyer'])->name('events.flyer.upload');
    Route::delete('/events/{event}/flyer',[EventController::class, 'removeFlyer'])->name('events.flyer.remove');

    Route::get   ('/schedule',                                 [\App\Http\Controllers\ScheduleController::class, 'index'])->name('schedule.index');

    // All chatty XHR endpoints — throttle to 120/min per authenticated user (generous for drag-edit)
    Route::middleware('throttle:120,1')->group(function () {
        Route::get   ('/api/suggestions',     [BulletinController::class, 'suggestions'])->name('suggestions');
        Route::get   ('/api/people',          [BulletinController::class, 'listPeople'])->name('people.list');
        Route::post  ('/api/people',          [BulletinController::class, 'addPerson'])->name('people.add');
        Route::patch ('/api/people',          [BulletinController::class, 'renamePerson'])->name('people.rename');
        Route::post  ('/api/people/block',    [BulletinController::class, 'blockPerson'])->name('people.block');
        Route::post  ('/api/people/unblock',  [BulletinController::class, 'unblockPerson'])->name('people.unblock');

        Route::get   ('/api/schedule/{deptId}',                    [\App\Http\Controllers\ScheduleController::class, 'data'])->whereNumber('deptId');
        Route::post  ('/api/schedule/{deptId}/members',            [\App\Http\Controllers\ScheduleController::class, 'storeMember'])->whereNumber('deptId');
        Route::patch ('/api/schedule/members/{member}',            [\App\Http\Controllers\ScheduleController::class, 'updateMember']);
        Route::delete('/api/schedule/members/{member}',            [\App\Http\Controllers\ScheduleController::class, 'deleteMember']);
        Route::post  ('/api/schedule/assignments',                 [\App\Http\Controllers\ScheduleController::class, 'storeAssignment']);
        Route::patch ('/api/schedule/assignments/{assignment}',    [\App\Http\Controllers\ScheduleController::class, 'updateAssignment']);
        Route::delete('/api/schedule/assignments/{assignment}',    [\App\Http\Controllers\ScheduleController::class, 'deleteAssignment']);
    });
});

Route::middleware('auth')->group(function () {
    Route::get   ('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch ('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin hub + tools (super_admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::view  ('/admin',                        'admin.hub')->name('admin.hub');
        Route::view  ('/admin/names',                  'admin.names')->name('admin.names');
        Route::get   ('/admin/logs',                   [\App\Http\Controllers\AdminLogsController::class, 'index'])->name('admin.logs');
        Route::get   ('/admin/security',                          [\App\Http\Controllers\AdminSecurityController::class, 'index'])->name('admin.security');
        Route::post  ('/admin/security/block',                    [\App\Http\Controllers\AdminSecurityController::class, 'block'])->name('admin.security.block');
        Route::post  ('/admin/security/unblock/{id}',             [\App\Http\Controllers\AdminSecurityController::class, 'unblock'])->name('admin.security.unblock')->where('id', '[0-9]+');
        Route::get   ('/admin/users',                  [\App\Http\Controllers\AdminUsersController::class, 'index'])->name('admin.users');
        Route::post  ('/admin/users',                  [\App\Http\Controllers\AdminUsersController::class, 'store'])->name('admin.users.store');
        Route::patch ('/admin/users/{user}/pin',       [\App\Http\Controllers\AdminUsersController::class, 'updatePin'])->name('admin.users.pin');
        Route::delete('/admin/users/{user}/pin',       [\App\Http\Controllers\AdminUsersController::class, 'clearPin'])->name('admin.users.pin.clear');
        // Editable site pages (markdown-backed, image-uploadable)
        Route::get   ('/admin/pages',                   [\App\Http\Controllers\AdminPagesController::class, 'index'])->name('admin.pages.index');
        Route::post  ('/admin/settings/theme', function (\Illuminate\Http\Request $request) {
            $data = $request->validate(['theme' => 'required|in:default,communion,easter,christmas,mothers,thanksgiving']);
            \App\Models\AppSetting::set('site_theme', $data['theme']);
            \App\Models\AuditLog::record(event: 'site_theme_changed', userId: auth()->id(), description: 'Site theme set to: ' . $data['theme']);
            return response()->json(['ok' => true, 'theme' => $data['theme']]);
        })->name('admin.settings.theme');
        Route::get   ('/admin/anthropic-usage', function () {
            $now = \Carbon\Carbon::now('America/New_York');
            $startToday = $now->copy()->startOfDay()->setTimezone('UTC');
            $start7d    = $now->copy()->subDays(7)->setTimezone('UTC');
            $startMonth = $now->copy()->startOfMonth()->setTimezone('UTC');
            $start30d   = $now->copy()->subDays(30)->setTimezone('UTC');
            return view('admin.anthropic-usage', [
                'totalToday'  => \App\Models\AnthropicUsageLog::totalSpend($startToday),
                'callsToday'  => \App\Models\AnthropicUsageLog::where('created_at','>=',$startToday)->count(),
                'total7d'     => \App\Models\AnthropicUsageLog::totalSpend($start7d),
                'calls7d'     => \App\Models\AnthropicUsageLog::where('created_at','>=',$start7d)->count(),
                'totalMonth'  => \App\Models\AnthropicUsageLog::totalSpend($startMonth),
                'callsMonth'  => \App\Models\AnthropicUsageLog::where('created_at','>=',$startMonth)->count(),
                'totalAll'    => (float) \App\Models\AnthropicUsageLog::sum('cost_usd'),
                'callsAll'    => \App\Models\AnthropicUsageLog::count(),
                'bySource'    => \App\Models\AnthropicUsageLog::spendBySource($start30d),
                'byModel'     => \App\Models\AnthropicUsageLog::spendByModel($start30d),
                'recent'      => \App\Models\AnthropicUsageLog::orderByDesc('created_at')->limit(50)->get(),
            ]);
        })->name('admin.anthropic-usage');
        Route::get   ('/admin/changelog',               function () {
            $path = base_path('docs/CHANGELOG.md');
            $md = is_file($path) ? file_get_contents($path) : '# No CHANGELOG.md yet';
            $converter = new \League\CommonMark\CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            $html = (string) $converter->convert($md);
            return view('admin.changelog', ['html' => $html]);
        })->name('admin.changelog');
        Route::get   ('/admin/pages/{slug}/edit',       [\App\Http\Controllers\AdminPagesController::class, 'edit'])->name('admin.pages.edit');
        Route::patch ('/admin/pages/{slug}',            [\App\Http\Controllers\AdminPagesController::class, 'update'])->name('admin.pages.update');
        Route::post  ('/admin/pages/upload-image',      [\App\Http\Controllers\AdminPagesController::class, 'uploadImage'])->name('admin.pages.upload-image');

        // Peace ministry admin (edit any sermon field, Q&As, scriptures)
        Route::get   ('/admin/peace',                            [\App\Http\Controllers\AdminPeaceController::class, 'index'])->name('admin.peace.index');
        Route::get   ('/admin/peace/schedule',                   [\App\Http\Controllers\AdminPeaceController::class, 'schedule'])->name('admin.peace.schedule');
        Route::get   ('/admin/peace/analytics',                  [\App\Http\Controllers\AdminPeaceController::class, 'analytics'])->name('admin.peace.analytics');
        Route::get   ('/admin/peace/subscribers',                [\App\Http\Controllers\AdminPeaceController::class, 'subscribers'])->name('admin.peace.subscribers');
        Route::get ('/admin/peace/submissions',                  [\App\Http\Controllers\AdminPeaceController::class, 'submissions'])->name('admin.peace.submissions');
        Route::post('/admin/peace/submissions/{id}',             [\App\Http\Controllers\AdminPeaceController::class, 'submissionsUpdate'])->name('admin.peace.submissions.update')->where('id', '[0-9]+');
        Route::post  ('/admin/peace/schedule/trigger',           [\App\Http\Controllers\AdminPeaceController::class, 'scheduleTrigger'])->name('admin.peace.schedule.trigger');
        Route::get   ('/admin/peace/{slug}/edit',                [\App\Http\Controllers\AdminPeaceController::class, 'edit'])->name('admin.peace.edit');
        Route::post  ('/admin/peace/{slug}',                     [\App\Http\Controllers\AdminPeaceController::class, 'update'])->name('admin.peace.update');
        Route::post  ('/admin/peace/{slug}/qa',                  [\App\Http\Controllers\AdminPeaceController::class, 'addQa'])->name('admin.peace.qa.add');
        Route::patch ('/admin/peace/{slug}/qa/{qa}',             [\App\Http\Controllers\AdminPeaceController::class, 'updateQa'])->name('admin.peace.qa.update');
        Route::delete('/admin/peace/{slug}/qa/{qa}',             [\App\Http\Controllers\AdminPeaceController::class, 'destroyQa'])->name('admin.peace.qa.destroy');
        Route::post  ('/admin/peace/{slug}/scripture',           [\App\Http\Controllers\AdminPeaceController::class, 'addScripture'])->name('admin.peace.scripture.add');
        Route::delete('/admin/peace/{slug}/scripture/{id}',      [\App\Http\Controllers\AdminPeaceController::class, 'destroyScripture'])->name('admin.peace.scripture.destroy');
        Route::post  ('/admin/peace/{slug}/trim',                [\App\Http\Controllers\AdminPeaceController::class, 'trim'])->name('admin.peace.trim');
        Route::post  ('/admin/peace/{slug}/recompress',          [\App\Http\Controllers\AdminPeaceController::class, 'recompress'])->name('admin.peace.recompress');

        // Peace polls — David-style multiple choice that ties feelings back to scripture
        Route::get   ('/admin/peace/polls',                       [\App\Http\Controllers\AdminPeacePollsController::class, 'index'])->name('admin.peace.polls.index');
        Route::get   ('/admin/peace/polls/create',                [\App\Http\Controllers\AdminPeacePollsController::class, 'create'])->name('admin.peace.polls.create');
        Route::post  ('/admin/peace/polls',                       [\App\Http\Controllers\AdminPeacePollsController::class, 'store'])->name('admin.peace.polls.store');
        Route::get   ('/admin/peace/polls/{poll}/edit',           [\App\Http\Controllers\AdminPeacePollsController::class, 'edit'])->name('admin.peace.polls.edit');
        Route::post  ('/admin/peace/polls/{poll}',                [\App\Http\Controllers\AdminPeacePollsController::class, 'update'])->name('admin.peace.polls.update');
        Route::delete('/admin/peace/polls/{poll}',                [\App\Http\Controllers\AdminPeacePollsController::class, 'destroy'])->name('admin.peace.polls.destroy');

        Route::get   ("/admin/slides",                 [\App\Http\Controllers\AdminSlidesController::class, "index"])->name("admin.slides.index");
        Route::post  ("/admin/slides",                 [\App\Http\Controllers\AdminSlidesController::class, "store"])->name("admin.slides.store");
        Route::patch ("/admin/slides/{slide}",         [\App\Http\Controllers\AdminSlidesController::class, "update"])->name("admin.slides.update");
        Route::delete("/admin/slides/{slide}",         [\App\Http\Controllers\AdminSlidesController::class, "destroy"])->name("admin.slides.destroy");
        // Sermon archive (Peace Notes)
        Route::get   ('/admin/sermons',                 [\App\Http\Controllers\AdminSermonsController::class, 'index'])->name('admin.sermons.index');
        Route::get   ('/admin/sermons/create',          [\App\Http\Controllers\AdminSermonsController::class, 'create'])->name('admin.sermons.create');
        Route::post  ('/admin/sermons',                 [\App\Http\Controllers\AdminSermonsController::class, 'store'])->name('admin.sermons.store');
        Route::get   ('/admin/sermons/{sermon}/edit',   [\App\Http\Controllers\AdminSermonsController::class, 'edit'])->name('admin.sermons.edit');
        Route::patch ('/admin/sermons/{sermon}',        [\App\Http\Controllers\AdminSermonsController::class, 'update'])->name('admin.sermons.update');
        Route::delete('/admin/sermons/{sermon}',        [\App\Http\Controllers\AdminSermonsController::class, 'destroy'])->name('admin.sermons.destroy');
        Route::post  ('/admin/sermons/upload-image',    [\App\Http\Controllers\AdminSermonsController::class, 'uploadImage'])->name('admin.sermons.upload-image');

        // Media library — central pool of uploaded images + audio with picker integration
        Route::get   ('/admin/media',                   [\App\Http\Controllers\AdminMediaController::class, 'index'])->name('admin.media.index');
        Route::post  ('/admin/media',                   [\App\Http\Controllers\AdminMediaController::class, 'store'])->name('admin.media.store');
        Route::delete('/admin/media/{media}',           [\App\Http\Controllers\AdminMediaController::class, 'destroy'])->name('admin.media.destroy');

        // First-party analytics — page_views aggregated dashboard
        Route::get   ('/admin/analytics',               [\App\Http\Controllers\AdminAnalyticsController::class, 'index'])->name('admin.analytics');

        // Proposals — staging area for previewing UX changes before they ship.
        Route::get  ('/proposals/bulletin-week-mode', [\App\Http\Controllers\ProposalsController::class, 'bulletinWeekMode'])->name('proposals.bulletin-week-mode');
        Route::post ('/proposals/bulletin-week-mode/vote', [\App\Http\Controllers\ProposalsController::class, 'voteBulletinWeekMode'])->name('proposals.bulletin-week-mode.vote');
        Route::get   ('/admin/inbox',                   [\App\Http\Controllers\AdminInboxController::class, 'index'])->name('admin.inbox');
    Route::get  ('/admin/messages',                  [\App\Http\Controllers\AdminMessagesController::class, 'index'])->name('admin.messages');
    Route::post ('/admin/messages/prayer/{id}/read', [\App\Http\Controllers\AdminMessagesController::class, 'markPrayerRead'])->name('admin.messages.prayer.read')->whereNumber('id');
    Route::post ('/admin/messages/contact/{id}/read',[\App\Http\Controllers\AdminMessagesController::class, 'markContactRead'])->name('admin.messages.contact.read')->whereNumber('id');
        Route::patch ('/admin/inbox/{ticket}/close',    [\App\Http\Controllers\AdminInboxController::class, 'close'])->name('admin.inbox.close');

        Route::get   ('/admin/lessons',                [\App\Http\Controllers\AdminLessonsController::class, 'index'])->name('admin.lessons');
        Route::post  ('/admin/lessons',                [\App\Http\Controllers\AdminLessonsController::class, 'store'])->name('admin.lessons.store');
        Route::post  ('/admin/lessons/{lesson}/sync',  [\App\Http\Controllers\AdminLessonsController::class, 'sync'])->name('admin.lessons.sync');
        Route::delete('/admin/lessons/{lesson}',       [\App\Http\Controllers\AdminLessonsController::class, 'destroy'])->name('admin.lessons.destroy');
    });

    // Direct line — feedback form + ticket history
    Route::get   ('/feedback', [\App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::post  ('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->middleware('throttle:10,1')->name('feedback.store');
    Route::get   ('/feedback/{ticket}/attach/{idx}', [\App\Http\Controllers\FeedbackController::class, 'attachment'])->where('idx', '[0-9]+')->name('feedback.attachment');
    // Admin-only ticket actions: gated at the middleware layer (defense-in-depth — controller also re-checks)
    Route::middleware('role:clerk')->group(function () {
        Route::patch ('/feedback/{ticket}/close',  [\App\Http\Controllers\FeedbackController::class, 'close'])->name('feedback.close');
        Route::patch ('/feedback/{ticket}/reopen', [\App\Http\Controllers\FeedbackController::class, 'reopen'])->name('feedback.reopen');
    });
});

require __DIR__.'/auth.php';
