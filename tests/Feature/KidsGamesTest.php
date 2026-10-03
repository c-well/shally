<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackPageView;
use App\Models\GameLevel;
use App\Models\GamePlayer;
use App\Models\GameProgress;
use App\Models\GameRun;
use App\Models\User;
use App\Services\KidsGameEngine;
use App\Services\KidsPlayerEvidence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class KidsGamesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32)), 'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        $this->withoutMiddleware(TrackPageView::class);
        foreach (['0001_01_01_000000_create_users_table.php', '2026_04_18_224910_add_role_to_users_table.php', '2026_04_24_200000_create_audit_logs_table.php', '2026_05_23_020525_create_app_settings_table.php', '2026_08_25_010000_create_service_times_table.php', '2026_06_26_create_games.php', '2026_10_02_170000_add_kids_activity_evidence.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }

    private function level(string $type = 'hidden_words', string $verse = 'God is love'): GameLevel
    {
        return GameLevel::create(['game_type' => $type, 'age_band' => 'little', 'book' => '1 John', 'reference' => '1 John 4:8', 'verse_text' => $verse, 'title' => 'God is Love', 'is_active' => true]);
    }

    private function player(string $review = 'auto'): GamePlayer
    {
        return GamePlayer::create(['name' => 'Test child', 'token' => (string) Str::uuid(), 'review_status' => $review]);
    }

    private function start(GamePlayer $p, GameLevel $l): array
    {
        return $this->postJson('/kids/begin', ['token' => $p->token, 'level_id' => $l->id])->assertOk()->json();
    }

    private function move(GamePlayer $p, array $run, int $seq, array $event)
    {
        if (($event['type'] ?? null) === 'reveal' && ! array_key_exists('word', $event)) {
            $event['word'] = $run['challenge']['words'][$event['index']] ?? '';
        }

        return $this->postJson('/kids/save', ['token' => $p->token, 'run_token' => $run['run_token'], 'sequence' => $seq, 'event' => $event, 'stars' => 3, 'completed' => true, 'score' => 999999]);
    }

    public function test_hidden_words_rewards_only_completion_and_retries_do_not_duplicate(): void
    {
        $p = $this->player();
        $l = $this->level();
        $run = $this->start($p, $l);
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 0])->assertOk()->assertJsonPath('total_stars', 0)->assertJsonPath('state.completed', false);
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 0])->assertOk()->assertJsonPath('sequence', 1);
        $this->assertSame(1, GameRun::first()->actions);
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 1])->assertStatus(409);
        $this->move($p, $run, 2, ['type' => 'reveal', 'index' => 1])->assertOk()->assertJsonPath('total_stars', 0);
        $this->travel(12)->seconds();
        $this->move($p, $run, 3, ['type' => 'reveal', 'index' => 2])->assertOk()->assertJsonPath('total_stars', 3)->assertJsonPath('state.completed', true);
        $this->move($p, $run, 3, ['type' => 'reveal', 'index' => 2])->assertOk()->assertJsonPath('total_stars', 3);
        $this->assertTrue(GameRun::first()->qualifies);
        $again = $this->start($p, $l);
        foreach (range(0, 2) as $i) {
            $this->move($p, $again, $i + 1, ['type' => 'reveal', 'index' => $i])->assertOk();
        }
        $this->assertEquals(3, GameProgress::first()->earned_stars);
        $this->assertSame(1, GameProgress::count());
    }

    public function test_cannot_skip_moves_reuse_other_player_session_or_save_without_session(): void
    {
        $p = $this->player();
        $run = $this->start($p, $this->level());
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 2])->assertUnprocessable();
        $this->assertSame(1, GameRun::first()->invalid_actions);
        $this->move($p, $run, 2, ['type' => 'reveal', 'index' => 0])->assertStatus(409);
        $other = $this->player();
        $this->move($other, $run, 1, ['type' => 'reveal', 'index' => 0])->assertNotFound();
        $this->postJson('/kids/save', ['token' => $p->token, 'level_id' => 1, 'stars' => 3, 'completed' => true])->assertStatus(409);
        $this->assertSame(0, GameProgress::count());
    }

    public function test_memory_awards_after_all_real_pairs_not_first_match(): void
    {
        $p = $this->player();
        $run = $this->start($p, $this->level('memory_match', 'God created heaven earth'));
        $cards = $run['challenge']['cards'];
        $seq = 0;
        $pairs = [];
        foreach ($cards as $i => $w) {
            $pairs[$w][] = $i;
        }
        foreach (array_values($pairs) as $n => $pair) {
            $this->move($p, $run, ++$seq, ['type' => 'flip', 'index' => $pair[0]])->assertOk();
            $this->move($p, $run, ++$seq, ['type' => 'flip', 'index' => $pair[1]])->assertOk()->assertJsonPath('total_stars', $n === count($pairs) - 1 ? 3 : 0);
        }
    }

    public function test_word_search_checks_the_actual_grid_and_every_target(): void
    {
        $p = $this->player();
        $run = $this->start($p, $this->level('word_search', 'God created the heaven and earth'));
        $this->move($p, $run, 1, ['type' => 'word', 'start' => [0, 0], 'end' => [0, 0]])->assertUnprocessable();
        $seq = 0;
        $positions = $run['challenge']['positions'];
        foreach ($positions as [$a,$b]) {
            $this->move($p, $run, ++$seq, ['type' => 'word', 'start' => $a, 'end' => $b])->assertOk()->assertJsonPath('total_stars', $seq === count($positions) ? 3 : 0);
        }
    }

    public function test_tetris_checks_physical_limits_and_calculates_score(): void
    {
        $p = $this->player();
        $run = $this->start($p, $this->level('verse_tetris', 'I can do all things through Christ which strengtheneth me'));
        $this->move($p, $run, 1, ['type' => 'piece', 'lines' => 4])->assertUnprocessable();
        for ($i = 1; $i <= 7; $i++) {
            $this->move($p, $run, $i, ['type' => 'piece', 'lines' => 0])->assertOk();
        }
        $this->move($p, $run, 8, ['type' => 'piece', 'lines' => 1])->assertOk()->assertJsonPath('total_stars', 1)->assertJsonPath('state.score', 40);
        $this->move($p, $run, 9, ['type' => 'piece', 'lines' => 2])->assertOk()->assertJsonPath('total_stars', 1)->assertJsonPath('state.score', 140);
        for ($i = 10; $i <= $run['challenge']['total']; $i++) {
            $response = $this->move($p, $run, $i, ['type' => 'piece', 'lines' => 0])->assertOk();
        }
        $response->assertJsonPath('total_stars', 3)->assertJsonPath('state.completed', true);
    }

    public function test_trail_checks_chosen_word_and_tetris_preserves_verse_in_phrases(): void
    {
        $p = $this->player();
        $run = $this->start($p, $this->level());
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 0, 'word' => 'wrong'])->assertUnprocessable();
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 0, 'word' => 'God'])->assertOk()->assertJsonPath('state.progress', 1);
        $verse = 'I am the way, the truth, and the life.';
        $blocks = $this->start($p, $this->level('verse_tetris', $verse));
        $this->assertSame('letter_blocks', $blocks['challenge']['mode']);
        $this->assertSame(implode('', preg_split('/[^\p{L}\p{N}]+/u', $verse)), implode('', array_column($blocks['challenge']['letter_steps'], 'letters')));
        $this->assertSame($verse, implode(' ', $blocks['challenge']['phrases']));
        $legacy = app(KidsGameEngine::class)->move('hidden_words', ['words' => ['God'], 'total' => 1], [], ['type' => 'reveal', 'index' => 0]);
        $this->assertSame(1, $legacy['progress']);
    }

    public function test_letter_blocks_build_from_first_piece_resume_and_do_not_double_count_retries(): void
    {
        $p = $this->player();
        $level = $this->level('verse_tetris', 'God loves us.');
        $run = $this->start($p, $level);
        $this->assertSame(['God', 'love', 's', 'us'], array_column($run['challenge']['letter_steps'], 'letters'));
        $this->move($p, $run, 1, ['type' => 'piece', 'lines' => 0])->assertOk()->assertJsonPath('state.words_built', 1)->assertJsonPath('state.progress', 1)->assertJsonPath('total_stars', 1);
        $this->move($p, $run, 1, ['type' => 'piece', 'lines' => 0])->assertOk()->assertJsonPath('state.progress', 1);
        $this->postJson('/kids/begin', ['token' => $p->token, 'level_id' => $level->id, 'run_token' => $run['run_token']])->assertOk()->assertJsonPath('run_token', $run['run_token'])->assertJsonPath('sequence', 1)->assertJsonPath('state.words_built', 1);
        $this->move($p, $run, 2, ['type' => 'piece', 'lines' => 0])->assertOk()->assertJsonPath('state.words_built', 1);
        $this->move($p, $run, 3, ['type' => 'piece', 'lines' => 0])->assertOk()->assertJsonPath('state.words_built', 2);
        $this->move($p, $run, 4, ['type' => 'piece', 'lines' => 0])->assertOk()->assertJsonPath('state.words_built', 3)->assertJsonPath('state.completed', true)->assertJsonPath('total_stars', 3);
        $legacy = app(KidsGameEngine::class)->move('verse_tetris', ['total' => 3], ['pieces' => 7, 'lines' => 0], ['type' => 'piece', 'lines' => 1]);
        $this->assertSame(1, $legacy['progress']);
        $this->assertFalse($legacy['completed']);
    }

    public function test_preview_stays_out_of_leaderboard_and_never_claims_known_identity(): void
    {
        $registered = $this->postJson('/kids/register', ['name' => 'Preview', 'preview' => true, 'review_status' => 'known'])->assertOk()->json();
        $p = GamePlayer::where('token', $registered['token'])->first();
        $this->assertSame('test', $p->review_status);
        $this->postJson('/kids/profile', ['token' => $p->token])->assertOk()->assertJsonPath('preview', true);
        $run = $this->start($p, $this->level());
        foreach (range(0, 2) as $i) {
            $this->move($p, $run, $i + 1, ['type' => 'reveal', 'index' => $i])->assertOk()->assertJsonPath('total_stars', 0);
        }
        $this->getJson('/kids/leaderboard')->assertOk()->assertExactJson(['leaders' => []]);
    }

    public function test_old_stars_are_preserved_but_not_claimed_as_new_checked_rewards(): void
    {
        $p = $this->player();
        $p->total_stars = 3;
        $p->save();
        $l = $this->level();
        GameProgress::create(['game_player_id' => $p->id, 'game_level_id' => $l->id, 'stars' => 3]);
        $this->postJson('/kids/profile', ['token' => $p->token])->assertOk()->assertJsonPath('total_stars', 0);
        $this->assertEquals(3, $p->fresh()->total_stars);
        $this->assertEquals(3, GameProgress::first()->stars);
        $this->getJson('/kids/leaderboard')->assertExactJson(['leaders' => []]);
    }

    public function test_resume_returns_same_puzzle_state_and_inactive_or_changed_levels_stop(): void
    {
        $p = $this->player();
        $l = $this->level();
        $run = $this->start($p, $l);
        $this->move($p, $run, 1, ['type' => 'reveal', 'index' => 0])->assertOk();
        $this->postJson('/kids/begin', ['token' => $p->token, 'level_id' => $l->id, 'run_token' => $run['run_token']])->assertOk()->assertJsonPath('sequence', 1)->assertJsonPath('state.progress', 1);
        $l->verse_text = 'God loves us';
        $l->save();
        $this->move($p, $run, 2, ['type' => 'reveal', 'index' => 1])->assertStatus(409);
        $l->is_active = false;
        $l->save();
        $this->postJson('/kids/begin', ['token' => $p->token, 'level_id' => $l->id])->assertNotFound();
    }

    public function test_leaderboard_only_returns_names_and_checked_stars_and_hides_reviewed_tests(): void
    {
        $p = $this->player();
        $run = $this->start($p, $this->level());
        foreach (range(0, 2) as $i) {
            $this->move($p, $run, $i + 1, ['type' => 'reveal', 'index' => $i]);
        }
        $this->getJson('/kids/leaderboard')->assertExactJson(['leaders' => [['name' => 'Test child', 'total_stars' => 3]]]);
        $p->review_status = 'test';
        $p->save();
        $this->getJson('/kids/leaderboard')->assertExactJson(['leaders' => []]);
    }

    public function test_player_admin_requires_super_admin_and_records_review(): void
    {
        $p = $this->player();
        $this->get('/admin/games/players')->assertRedirect();
        $user = User::factory()->create();
        $user->role = 'clerk';
        $user->save();
        $this->actingAs($user)->get('/admin/games/players')->assertForbidden();
        $this->patch('/admin/games/players/'.$p->id, ['review_status' => 'known'])->assertForbidden();
        $user->role = 'super_admin';
        $user->save();
        $this->withoutExceptionHandling();
        $this->actingAs($user)->get('/admin/games/players')->assertOk()->assertSee('Not enough evidence')->assertDontSee($p->token);
        $this->patch('/admin/games/players/'.$p->id, ['review_status' => 'known'])->assertRedirect();
        $this->assertSame('known', $p->fresh()->review_status);
        $this->assertSame($user->id, $p->fresh()->reviewed_by);
        $this->assertSame(1, DB::table('audit_logs')->where('event', 'kids_player_review')->count());
    }

    public function test_confidence_is_cautious_and_respects_staff_review(): void
    {
        $this->assertSame('Not enough evidence', KidsPlayerEvidence::assess('auto', 0, 0, 2, 1)[0]);
        $this->assertSame('Likely player', KidsPlayerEvidence::assess('auto', 1, 0, 10, 1)[0]);
        $this->assertSame('Needs review', KidsPlayerEvidence::assess('auto', 1, 10, 5, 1)[0]);
        $this->assertSame('Known to the team', KidsPlayerEvidence::assess('known', 0, 10, 0, 0)[0]);
        $this->assertSame('Test account', KidsPlayerEvidence::assess('test', 1, 0, 20, 2)[0]);
    }

    public function test_generated_puzzles_never_drop_words(): void
    {
        $engine = new KidsGameEngine;
        $level = $this->level('word_search', 'In the beginning God created the heaven and the earth');
        for ($i = 0; $i < 30; $i++) {
            $c = $engine->challenge($level);
            $s = [];
            $this->assertSame(count($level->keywords()), $c['total']);
            foreach ($c['positions'] as [$a,$b]) {
                $s = $engine->move('word_search', $c, $s, ['type' => 'word', 'start' => $a, 'end' => $b]);
            }
            $this->assertTrue($s['completed']);
        }
    }

    public function test_expired_session_starts_fresh_instead_of_resuming_an_unsavable_game(): void
    {
        $p = $this->player();
        $l = $this->level();
        $run = $this->start($p, $l);
        GameRun::query()->update(['created_at' => now()->subHours(25), 'updated_at' => now()]);
        $next = $this->postJson('/kids/begin', ['token' => $p->token, 'level_id' => $l->id, 'run_token' => $run['run_token']])->assertOk()->json();
        $this->assertNotSame($run['run_token'], $next['run_token']);
        $this->move($p, $next, 1, ['type' => 'reveal', 'index' => 0])->assertOk();
    }

    public function test_retention_only_prunes_old_sessions(): void
    {
        $p = $this->player();
        $l = $this->level();
        $run = $this->start($p, $l);
        GameRun::query()->update(['created_at' => now()->subDays(91)]);
        $this->artisan('model:prune', ['--model' => [GameRun::class]])->assertExitCode(0);
        $this->assertSame(0, GameRun::count());
        $this->assertSame(1, GamePlayer::count());
    }
    public function test_escape_room_requires_discoveries_correct_solutions_and_resumes(): void
    {
        $p = $this->player();
        $level = $this->level('escape_room');
        $run = $this->start($p, $level);
        $this->assertSame('philippi_v1', $run['challenge']['mode']);
        $seq = 1;
        $this->move($p, $run, $seq++, ['type' => 'solve', 'room' => 0, 'answer' => [2, 4, 3, 1, 0]])->assertOk()->assertJsonPath('state.progress', 0);
        $this->move($p, $run, $seq, ['type' => 'inspect', 'room' => 2, 'object' => 'bread'])->assertUnprocessable();
        foreach (['window', 'scroll', 'bench'] as $object) {
            $this->move($p, $run, $seq++, ['type' => 'inspect', 'room' => 0, 'object' => $object])->assertOk();
        }
        $wrong = ['type' => 'solve', 'room' => 0, 'answer' => [0, 1, 2, 3, 4]];
        $this->move($p, $run, $seq++, $wrong)->assertOk()->assertJsonPath('state.progress', 0)->assertJsonPath('state.completed', false);
        $this->move($p, $run, $seq++, ['type' => 'solve', 'room' => 0, 'answer' => [2, 4, 3, 1, 0]])->assertOk()->assertJsonPath('state.progress', 1);
        $this->postJson('/kids/begin', ['token' => $p->token, 'level_id' => $level->id, 'run_token' => $run['run_token']])->assertOk()->assertJsonPath('state.progress', 1)->assertJsonCount(3, 'state.discovered');
        foreach (['door', 'chains', 'lamp'] as $object) {
            $this->move($p, $run, $seq++, ['type' => 'inspect', 'room' => 1, 'object' => $object])->assertOk();
        }
        $this->move($p, $run, $seq++, ['type' => 'solve', 'room' => 1, 'answer' => ['WE', 'ARE', 'ALL', 'HERE']])->assertOk()->assertJsonPath('state.progress', 2);
        foreach (['water', 'scroll', 'bread'] as $object) {
            $this->move($p, $run, $seq++, ['type' => 'inspect', 'room' => 2, 'object' => $object])->assertOk();
        }
        $this->move($p, $run, $seq++, ['type' => 'solve', 'room' => 2, 'item' => 'bread', 'target' => 'hear'])->assertOk()->assertJsonPath('state.progress', 2)->assertJsonCount(0, 'state.placed');
        foreach (['scroll' => 'hear', 'water' => 'care'] as $item => $target) {
            $this->move($p, $run, $seq++, ['type' => 'solve', 'room' => 2, 'item' => $item, 'target' => $target])->assertOk()->assertJsonPath('state.completed', false);
        }
        $this->travel(12)->seconds();
        $event = ['type' => 'solve', 'room' => 2, 'item' => 'bread', 'target' => 'share'];
        $this->move($p, $run, $seq, $event)->assertOk()->assertJsonPath('state.completed', true)->assertJsonPath('state.score', 3)->assertJsonPath('total_stars', 3);
        $this->move($p, $run, $seq, $event)->assertOk()->assertJsonPath('total_stars', 3);
        $this->assertSame(1, GameProgress::count());
    }

    public function test_game_audio_uses_only_published_service_windows(): void
    {
        \App\Models\ServiceTime::create(['name' => 'Worship', 'when_label' => 'SAT', 'days' => [6], 'live_from' => '10:50:00', 'live_until' => '14:30:00', 'is_published' => true]);
        \App\Models\ServiceTime::create(['name' => 'Hidden', 'when_label' => 'MON', 'days' => [1], 'live_from' => '02:12:00', 'live_until' => '03:13:00', 'is_published' => false]);
        $html = view('kids.audio')->render();
        preg_match('/const CHURCH_AUDIO = (.+);/', $html, $match);
        $audio = json_decode($match[1], true);
        $this->assertSame('America/New_York', $audio['timezone']);
        $this->assertSame([['days' => [6], 'from' => '10:50', 'until' => '14:30']], $audio['windows']);

    }

}
