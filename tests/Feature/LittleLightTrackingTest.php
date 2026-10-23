<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackPageView;
use App\Models\LittleLightRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LittleLightTrackingTest extends TestCase
{
    private const API = '/api/little-light/tracking/';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32)), 'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware(TrackPageView::class);
        foreach (['0001_01_01_000000_create_users_table.php','2026_04_18_224910_add_role_to_users_table.php','2026_04_24_200000_create_audit_logs_table.php','2026_05_23_020525_create_app_settings_table.php','2026_10_23_180000_create_little_light_runs.php'] as $file) (require database_path('migrations/'.$file))->up();
    }

    private function start(int $chapter = 1, bool $preview = false): array
    {
        $d = ['run_token' => (string) Str::uuid(), 'browser_token' => (string) Str::uuid(), 'chapter' => $chapter, 'preview' => $preview];
        $this->postJson(self::API.'start', $d)->assertOk();
        return $d;
    }

    private function report(array $run, array $values = []): array
    {
        return array_replace(['run_token' => $run['run_token'], 'browser_token' => $run['browser_token'], 'sequence' => 1, 'active_seconds' => 0, 'furthest_percent' => 0, 'scrolls' => 0, 'lights' => 0, 'sheep' => 0, 'stones' => 0, 'stumbles' => 0, 'status' => 'playing'], $values);
    }

    public function test_start_is_idempotent_and_does_not_store_a_raw_browser_identifier(): void
    {
        $r = $this->start();
        $this->postJson(self::API.'start', $r)->assertOk();
        $this->assertSame(1, LittleLightRun::count());
        $run = LittleLightRun::first();
        $this->assertNotSame($r['browser_token'], $run->browser_hash);
        $this->assertSame(0, $run->active_seconds);
        $this->assertSame(0, LittleLightRun::engaged()->count());
        $this->postJson(self::API.'start', array_replace($r, ['chapter' => 3]))->assertConflict();
        $this->getJson(self::API.'bootstrap')->assertOk()->assertJsonStructure(['csrf'])->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_progress_is_cumulative_bounded_and_duplicate_or_stale_reports_cannot_regress_it(): void
    {
        $r = $this->start();
        $this->travel(20)->seconds();
        $d = $this->report($r, ['active_seconds' => 999, 'furthest_percent' => 51, 'scrolls' => 2, 'lights' => 8, 'stumbles' => 2]);
        $this->postJson(self::API.'progress', $d)->assertOk();
        $this->assertSame(20, LittleLightRun::first()->active_seconds);
        $this->assertSame(1, LittleLightRun::engaged()->count());
        $this->postJson(self::API.'progress', $d)->assertOk();
        $this->postJson(self::API.'progress', $this->report($r, ['sequence' => 2, 'status' => 'paused']))->assertOk();
        $run = LittleLightRun::first();
        $this->assertSame(51, $run->furthest_percent);
        $this->assertSame(2, $run->scrolls);
        $this->assertSame(20, $run->active_seconds);
        $this->assertSame('paused', $run->status);
        $this->postJson(self::API.'progress', $d)->assertOk();
        $this->assertSame('paused', $run->fresh()->status);
    }

    public function test_completion_requires_each_chapters_objective_and_is_terminal(): void
    {
        foreach ([1 => ['sheep' => 3], 2 => ['stones' => 5], 3 => ['scrolls' => 3]] as $chapter => $objective) {
            $r = $this->start($chapter);
            $this->travel(30)->seconds();
            $this->postJson(self::API.'progress', $this->report($r, ['active_seconds' => 25, 'furthest_percent' => 95, 'status' => 'complete']))->assertUnprocessable();
            $d = $this->report($r, $objective + ['active_seconds' => 25, 'furthest_percent' => $chapter === 2 ? 88 : 98, 'status' => 'complete']);
            $this->postJson(self::API.'progress', $d)->assertOk();
            $this->postJson(self::API.'progress', $this->report($r, ['sequence' => 2, 'status' => 'left']))->assertOk();
            $run = LittleLightRun::where('token', $r['run_token'])->first();
            $this->assertNotNull($run->completed_at);
            $this->assertSame(100, $run->furthest_percent);
            $this->assertSame('complete', $run->status);
        }
    }

    public function test_validation_and_browser_binding_reject_forged_or_expired_updates(): void
    {
        $r = $this->start();
        $this->postJson(self::API.'progress', $this->report($r, ['scrolls' => 4]))->assertUnprocessable();
        $this->postJson(self::API.'progress', $this->report($r, ['active_seconds' => -1]))->assertUnprocessable();
        $this->postJson(self::API.'progress', $this->report($r, ['browser_token' => (string) Str::uuid()]))->assertNotFound();
        $this->travel(25)->hours();
        $this->postJson(self::API.'progress', $this->report($r))->assertStatus(410);
    }

    private function staff(string $role = 'super_admin'): User
    {
        $u = new User(['name' => 'Staff test', 'email' => Str::uuid().'@example.test', 'password' => 'password']);
        $u->role = $role; $u->save(); return $u;
    }

    public function test_dashboard_is_private_excludes_tests_and_allows_staff_to_correct_test_classification(): void
    {
        $this->get('/admin/games/little-light')->assertRedirect('/login');
        $this->start(1, true);
        $ordinary = $this->start(2);
        $this->actingAs($this->staff('member'))->get('/admin/games/little-light')->assertForbidden();
        $this->actingAs($this->staff());
        $this->start(3); // Signed-in staff automatically tests.
        $this->get('/admin/games/little-light')->assertOk()->assertViewHas('totals', fn ($v) => $v['starts'] === 1 && $v['browsers'] === 1);
        $this->get('/admin/games/little-light?tests=1')->assertOk()->assertViewHas('totals', fn ($v) => $v['starts'] === 2);
        $run = LittleLightRun::where('token', $ordinary['run_token'])->first();
        $this->patch('/admin/games/little-light/'.$run->id, ['is_test' => true])->assertRedirect();
        $this->get('/admin/games/little-light')->assertOk()->assertViewHas('totals', fn ($v) => $v['starts'] === 0);
    }

    public function test_retention_prunes_only_attempts_older_than_ninety_days(): void
    {
        $this->start();
        $this->travel(91)->days();
        $this->start();
        $this->artisan('model:prune', ['--model' => [LittleLightRun::class]])->assertExitCode(0);
        $this->assertSame(1, LittleLightRun::count());
    }
}
