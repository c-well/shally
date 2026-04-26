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
     ->where('slug', '[a-z0-9-]+')->name('peace-notes.show');
Route::get ('/about',       fn () => view('about',       ['page' => \App\Models\Page::bySlug('about')]))->name('about');
Route::get ('/beliefs',     fn () => view('beliefs',     ['page' => \App\Models\Page::bySlug('beliefs')]))->name('beliefs');
Route::get ('/visit',       fn () => view('visit',       ['page' => \App\Models\Page::bySlug('visit')]))->name('visit');
Route::get ('/contact',  [\App\Http\Controllers\ContactController::class, 'show'])->name('contact.show');
Route::post('/contact',  [\App\Http\Controllers\ContactController::class, 'send'])
     ->middleware(['throttle:5,60', 'honeypot'])->name('contact.send');

// SEO infrastructure — full site indexing
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt',  [\App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

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
        Route::get   ('/admin/users',                  [\App\Http\Controllers\AdminUsersController::class, 'index'])->name('admin.users');
        Route::post  ('/admin/users',                  [\App\Http\Controllers\AdminUsersController::class, 'store'])->name('admin.users.store');
        Route::patch ('/admin/users/{user}/pin',       [\App\Http\Controllers\AdminUsersController::class, 'updatePin'])->name('admin.users.pin');
        Route::delete('/admin/users/{user}/pin',       [\App\Http\Controllers\AdminUsersController::class, 'clearPin'])->name('admin.users.pin.clear');
        // Editable site pages (markdown-backed, image-uploadable)
        Route::get   ('/admin/pages',                   [\App\Http\Controllers\AdminPagesController::class, 'index'])->name('admin.pages.index');
        Route::get   ('/admin/pages/{slug}/edit',       [\App\Http\Controllers\AdminPagesController::class, 'edit'])->name('admin.pages.edit');
        Route::patch ('/admin/pages/{slug}',            [\App\Http\Controllers\AdminPagesController::class, 'update'])->name('admin.pages.update');
        Route::post  ('/admin/pages/upload-image',      [\App\Http\Controllers\AdminPagesController::class, 'uploadImage'])->name('admin.pages.upload-image');

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
        Route::get   ('/admin/inbox',                   [\App\Http\Controllers\AdminInboxController::class, 'index'])->name('admin.inbox');
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
