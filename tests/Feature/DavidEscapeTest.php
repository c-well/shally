<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackPageView;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DavidEscapeTest extends TestCase
{
    public function test_adventure_renders_quiet_hours_controls_and_separate_game_tracking(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32)), 'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware(TrackPageView::class);
        (require database_path('migrations/2026_08_25_010000_create_service_times_table.php'))->up();
        $this->get('/kids/david-escape')->assertOk()->assertHeader('Cache-Control','no-store, private')
            ->assertSee('data-game="david_escape"',false)->assertSee('CHURCH_AUDIO',false)
            ->assertSee('Hold Space to jump')->assertSee('Parkour and rewind are imaginative gameplay')->assertSee('game.js?v=1',false);
    }
}
