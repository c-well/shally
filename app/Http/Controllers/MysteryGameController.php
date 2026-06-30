<?php
namespace App\Http\Controllers;

use App\Models\GamePlayer;
use App\Models\GameRoom;
use App\Models\GameRoomPlayer;
use App\Models\MysteryAnswer;
use App\Models\MysteryGuess;
use App\Models\MysteryInvestigation;
use App\Models\MysteryQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * "Undercover" — the hosted teen mystery. Everyone joins anonymously behind a
 * codename; the app asks questions and the room works out who is who, and who
 * the hidden Crook is (a hidden Cop helps). Realtime is short-poll (cPanel can't
 * run a WebSocket daemon). Design conviction: no one is ever asked to lie — the
 * Crook/Cop stay hidden by staying silent; the app does the concealing. No
 * elimination. See docs/spec-spot-the-counterfeit.md.
 */
class MysteryGameController extends Controller
{
    private const CODENAMES = [
        'Onyx', 'Jasper', 'Amber', 'Cedar', 'Flint', 'Sable', 'Cobalt', 'Hazel', 'Indigo', 'Slate',
        'Ember', 'Birch', 'Quartz', 'Cypress', 'Garnet', 'Ivory', 'Pearl', 'Coral', 'Opal', 'Reed',
        'Briar', 'Fern', 'Heron', 'Lark', 'Wren', 'Sparrow', 'Raven', 'Falcon', 'Otter', 'Bramble',
        'Juniper', 'Willow', 'Marlin', 'Cricket', 'Robin', 'Finch', 'Sage', 'Clover', 'Aspen', 'Thorn',
    ];
    private const COP_INVESTIGATIONS = 2;

    // ── pages ──
    public function landing(): View { return view('youth.landing'); }

    public function hostView(string $code): View
    {
        $room = GameRoom::where('code', strtoupper($code))->firstOrFail();
        return view('youth.host', ['room' => $room]);
    }

    public function playView(string $code): View
    {
        $room = GameRoom::where('code', strtoupper($code))->firstOrFail();
        return view('youth.play', ['room' => $room]);
    }

    // ── host: create / start / advance ──
    public function createRoom(Request $request): JsonResponse
    {
        $rounds = (int) $request->input('rounds', 8);
        $rounds = max(3, min(15, $rounds));

        do { $code = $this->code(); } while (GameRoom::where('code', $code)->exists());

        $room = GameRoom::create([
            'code'         => $code,
            'host_token'   => (string) Str::uuid(),
            'status'       => 'lobby',
            'round_no'     => 0,
            'rounds_total' => $rounds,
            'settings'     => ['used' => []],
        ]);

        return response()->json(['ok' => true, 'code' => $room->code, 'host_token' => $room->host_token]);
    }

    public function start(Request $request, string $code): JsonResponse
    {
        $room = $this->room($code);
        $this->hostOnly($request, $room);
        if ($room->status !== 'lobby') return response()->json(['ok' => false, 'error' => 'already started'], 422);

        $players = $room->players()->get();
        if ($players->count() < 3) return response()->json(['ok' => false, 'error' => 'need at least 3 players'], 422);

        $this->dealRoles($room, $players);
        $this->pickNextQuestion($room);
        $room->update(['status' => 'round_question', 'round_no' => 1]);

        return response()->json(['ok' => true]);
    }

    public function advance(Request $request, string $code): JsonResponse
    {
        $room = $this->room($code);
        $this->hostOnly($request, $room);

        switch ($room->status) {
            case 'round_question':
                $room->status = 'round_clues';
                break;
            case 'round_clues':
                if ($room->round_no < $room->rounds_total) {
                    $room->round_no++;
                    $this->pickNextQuestion($room);
                    $room->status = 'round_question';
                } else {
                    $room->status = 'accusation';
                }
                break;
            case 'accusation':
                $this->computeReveal($room);
                $room->status = 'revealed';
                break;
            case 'revealed':
                $room->status = 'ended';
                break;
        }
        $room->save();

        return response()->json(['ok' => true, 'status' => $room->status]);
    }

    // ── player actions ──
    public function join(Request $request, string $code): JsonResponse
    {
        $room = $this->room($code);
        if ($room->status !== 'lobby') return response()->json(['ok' => false, 'error' => 'This game has already started.'], 422);

        $name  = trim((string) $request->input('name'));
        $token = (string) ($request->input('token') ?: Str::uuid());
        if ($name === '' || mb_strlen($name) > 60) return response()->json(['ok' => false, 'error' => 'name'], 422);

        $player = GameRoomPlayer::firstOrCreate(
            ['game_room_id' => $room->id, 'token' => $token],
            ['name' => $name, 'role' => 'citizen', 'score' => 0]
        );
        if ($player->name !== $name) $player->update(['name' => $name]);

        return response()->json(['ok' => true, 'player_id' => $player->id, 'token' => $token, 'name' => $name]);
    }

    public function answer(Request $request, string $code): JsonResponse
    {
        $room   = $this->room($code);
        $player = $this->player($request, $room);
        if (! $player || $room->status !== 'round_question') return response()->json(['ok' => false], 422);

        $silent = $request->boolean('silent');
        MysteryAnswer::updateOrCreate(
            ['game_room_id' => $room->id, 'round_no' => $room->round_no, 'player_id' => $player->id],
            [
                'question_id'   => $room->current_question_id,
                'answer'        => $silent ? null : (string) $request->input('answer'),
                'stayed_silent' => $silent,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function guess(Request $request, string $code): JsonResponse
    {
        $room   = $this->room($code);
        $player = $this->player($request, $room);
        if (! $player) return response()->json(['ok' => false], 422);

        $codename = (string) $request->input('codename');
        $guessId  = $request->input('guessed_player_id');
        $valid    = $room->players()->where('codename', $codename)->exists();
        if (! $valid) return response()->json(['ok' => false], 422);

        if ($guessId) {
            MysteryGuess::updateOrCreate(
                ['game_room_id' => $room->id, 'guesser_player_id' => $player->id, 'target_codename' => $codename],
                ['guessed_player_id' => (int) $guessId]
            );
        } else {
            MysteryGuess::where(['game_room_id' => $room->id, 'guesser_player_id' => $player->id, 'target_codename' => $codename])->delete();
        }

        return response()->json(['ok' => true]);
    }

    public function investigate(Request $request, string $code): JsonResponse
    {
        $room   = $this->room($code);
        $player = $this->player($request, $room);
        if (! $player || $player->role !== 'cop') return response()->json(['ok' => false], 403);
        if (! in_array($room->status, ['round_question', 'round_clues'])) return response()->json(['ok' => false], 422);

        $used = MysteryInvestigation::where(['game_room_id' => $room->id, 'cop_player_id' => $player->id])->count();
        if ($used >= self::COP_INVESTIGATIONS) return response()->json(['ok' => false, 'error' => 'no investigations left'], 422);

        $target = $room->players()->where('codename', (string) $request->input('codename'))->first();
        if (! $target) return response()->json(['ok' => false], 422);

        $result = $target->role === 'crook';
        MysteryInvestigation::create([
            'game_room_id' => $room->id, 'cop_player_id' => $player->id,
            'target_codename' => $target->codename, 'result' => $result, 'round_no' => $room->round_no,
        ]);

        return response()->json(['ok' => true, 'codename' => $target->codename, 'result' => $result]);
    }

    public function accuse(Request $request, string $code): JsonResponse
    {
        $room   = $this->room($code);
        $player = $this->player($request, $room);
        if (! $player || $room->status !== 'accusation') return response()->json(['ok' => false], 422);

        $codename = (string) $request->input('codename');
        if (! $room->players()->where('codename', $codename)->exists()) return response()->json(['ok' => false], 422);
        $player->update(['crook_vote' => $codename]);

        return response()->json(['ok' => true]);
    }

    // ── polling state ──
    public function state(Request $request, string $code): JsonResponse
    {
        $room    = $this->room($code);
        $player  = $this->player($request, $room);
        $isHost  = $request->query('host') && hash_equals($room->host_token, (string) $request->query('host'));
        $players = $room->players()->orderBy('codename')->get();
        $revealed = in_array($room->status, ['revealed', 'ended']);

        $out = [
            'status'       => $room->status,
            'round_no'     => $room->round_no,
            'rounds_total' => $room->rounds_total,
            'player_count' => $players->count(),
            'is_host'      => $isHost,
            'roster'       => $players->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->shuffle()->values(),
            'codenames'    => $players->whereNotNull('codename')->pluck('codename')->values(),
        ];

        if ($room->status !== 'lobby') {
            $out['profiles'] = $this->profiles($room, $players, $revealed);
        }

        if ($room->status === 'round_question' && $room->current_question_id) {
            $q = $room->question;
            if ($q) {
                $out['question'] = ['prompt' => $q->prompt, 'kind' => $q->kind, 'options' => $q->options];
                $out['answered'] = MysteryAnswer::where(['game_room_id' => $room->id, 'round_no' => $room->round_no])->count();
            }
        }

        if ($room->status === 'accusation' || $revealed) {
            $out['accuse_count'] = $players->whereNotNull('crook_vote')->count();
        }

        if ($revealed) {
            $out['reveal'] = [
                'caught'   => (bool) ($room->settings['caught'] ?? false),
                'accused'  => $room->settings['accused_codename'] ?? null,
                'people'   => $players->map(fn ($p) => [
                    'codename' => $p->codename, 'name' => $p->name, 'role' => $p->role, 'score' => $p->score,
                ])->sortByDesc('score')->values(),
            ];
        }

        if ($player) {
            $you = [
                'player_id' => $player->id, 'name' => $player->name,
                'codename'  => $player->codename, 'role' => $player->role, 'score' => $player->score,
                'crook_vote' => $player->crook_vote,
                'guesses'   => MysteryGuess::where(['game_room_id' => $room->id, 'guesser_player_id' => $player->id])
                                  ->pluck('guessed_player_id', 'target_codename'),
            ];
            if ($room->status === 'round_question') {
                $a = MysteryAnswer::where(['game_room_id' => $room->id, 'round_no' => $room->round_no, 'player_id' => $player->id])->first();
                $you['answered'] = $a ? ($a->stayed_silent ? '__silent__' : $a->answer) : null;
            }
            if ($player->role === 'cop') {
                $invs = MysteryInvestigation::where(['game_room_id' => $room->id, 'cop_player_id' => $player->id])->get();
                $you['investigations']      = $invs->map(fn ($i) => ['codename' => $i->target_codename, 'result' => $i->result])->values();
                $you['investigations_left'] = max(0, self::COP_INVESTIGATIONS - $invs->count());
            }
            $out['you'] = $you;
        }

        return response()->json($out);
    }

    // ── helpers ──
    private function code(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPRSTUVWXYZ'; // no I O Q
        return collect(range(1, 4))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->join('');
    }

    private function room(string $code): GameRoom
    {
        return GameRoom::where('code', strtoupper($code))->firstOrFail();
    }

    private function player(Request $request, GameRoom $room): ?GameRoomPlayer
    {
        $token = $request->input('token') ?: $request->query('token');
        return $token ? $room->players()->where('token', $token)->first() : null;
    }

    private function hostOnly(Request $request, GameRoom $room): void
    {
        $token = (string) ($request->input('host_token') ?: $request->query('host'));
        abort_unless($token !== '' && hash_equals($room->host_token, $token), 403);
    }

    private function dealRoles(GameRoom $room, $players): void
    {
        $players = $players->shuffle()->values();
        $n = $players->count();
        $crooks = $n >= 14 ? 2 : 1;
        $cops   = $n >= 18 ? 2 : 1;
        $cops   = min($cops, max(0, $n - $crooks - 1));
        $codes  = collect(self::CODENAMES)->shuffle()->values();

        foreach ($players as $i => $p) {
            $role = $i < $crooks ? 'crook' : ($i < $crooks + $cops ? 'cop' : 'citizen');
            $p->update(['role' => $role, 'codename' => $codes[$i] ?? ('Agent' . ($i + 1))]);
        }
    }

    private function pickNextQuestion(GameRoom $room): void
    {
        $settings = $room->settings ?? [];
        $used     = $settings['used'] ?? [];
        $q = MysteryQuestion::active()->whereNotIn('id', $used)->inRandomOrder()->first()
           ?: MysteryQuestion::active()->inRandomOrder()->first();
        if (! $q) return;

        $used[] = $q->id;
        $settings['used'] = $used;
        $room->settings = $settings;
        $room->current_question_id = $q->id;
        $room->current_question_started_at = now();
        $room->save();
    }

    private function profiles(GameRoom $room, $players, bool $revealed): array
    {
        $answers = MysteryAnswer::where('game_room_id', $room->id)->get()->groupBy('player_id');
        $qIds    = $answers->flatten()->pluck('question_id')->filter()->unique();
        $qById   = MysteryQuestion::whereIn('id', $qIds)->get()->keyBy('id');

        return $players->map(function ($p) use ($answers, $qById, $revealed) {
            $rows = ($answers[$p->id] ?? collect())->sortBy('round_no')->map(fn ($a) => [
                'round'  => $a->round_no,
                'prompt' => optional($qById[$a->question_id] ?? null)->prompt,
                'answer' => $a->stayed_silent ? null : $a->answer,
            ])->values();
            $row = ['codename' => $p->codename, 'answers' => $rows];
            if ($revealed) { $row['name'] = $p->name; $row['role'] = $p->role; }
            return $row;
        })->sortBy('codename')->values()->all();
    }

    private function computeReveal(GameRoom $room): void
    {
        $players    = $room->players()->get();
        $byCodename = $players->keyBy('codename');

        // who-is-who scoring
        foreach (MysteryGuess::where('game_room_id', $room->id)->get() as $g) {
            $target  = $byCodename->get($g->target_codename);
            $correct = $target && (int) $g->guessed_player_id === (int) $target->id;
            $g->update(['is_correct' => $correct]);
            if ($correct) { GameRoomPlayer::where('id', $g->guesser_player_id)->increment('score', 10); }
        }

        // crook accusation — majority vote
        $tally   = $players->whereNotNull('crook_vote')->groupBy('crook_vote')->map->count();
        $accused = $tally->sortDesc()->keys()->first();
        $caught  = $accused && optional($byCodename->get($accused))->role === 'crook';

        foreach ($players as $p) {
            if ($p->role === 'crook') {
                if (! $caught) $p->increment('score', 30);
            } else {
                if ($caught) $p->increment('score', 20);
            }
            if ($p->role === 'cop' && $p->crook_vote && optional($byCodename->get($p->crook_vote))->role === 'crook') {
                $p->increment('score', 15);
            }

            // gentle stars on the shared profile (reuse cop_kid identity)
            if ($gp = GamePlayer::where('token', $p->token)->first()) {
                $win = $p->role === 'crook' ? ! $caught : $caught;
                $gp->increment('total_stars', 1 + ($win ? 1 : 0));
            }
        }

        $settings = $room->settings ?? [];
        $settings['caught'] = (bool) $caught;
        $settings['accused_codename'] = $accused;
        $room->settings = $settings;
        $room->save();
    }
}
