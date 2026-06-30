# Spec — Teen hidden-identity mystery game (working title: "Undercover")

Status: **Phases 1–4 SHIPPED 2026-06-28** (playable at /youth). Remaining: leader-vetted question content, menu placement, polish (reconnect, big-room tuning). Pivoted from a simple Kahoot vote room to a hidden-identity detective mystery (Karlon, 2026-06-28). Verse Tetris is live; this is the next teen build.

## The idea in one line
A live youth-night mystery: everyone joins **anonymously** (the app hides each kid behind a codename). The app quietly asks everyone questions and **leaks anonymized clues**; the room's job is to slowly figure out **who is who** — and, hardest of all, **who the Crook is**. There's also a hidden **Cop** trying to catch the crook. It takes time, it's social, it's a get-to-know-each-other engine wearing a mystery costume.

## The design conviction — NO ONE IS EVER ASKED TO LIE (this is the whole point)
Karlon questioned the merits of casting a kid as the villain who must deceive friends. The resolution: **the app does the concealing; the crook just stays silent.**
- Every identity is hidden behind a codename by the app. Players answer the app's questions **honestly, or stay silent.**
- The Crook's and the Cop's only tool is **withholding** — not revealing their role. **They are never instructed to lie, and lying is never rewarded.** Winning by staying concealed ≠ looking a friend in the eye and deceiving them.
- **No elimination.** Nobody is "killed" or kicked — this is a church game. Everyone plays the whole session; the hunt is pure deduction.

This is non-negotiable and the reason the game exists in this shape. Don't reintroduce a spoken-deception mechanic.

## Roles (app-assigned, secret, app-concealed)
- **Crook ×1** (×2 in big rooms) — wins by staying unidentified to the end. Tools: answers blandly or stays silent so the app has less to leak.
- **Cop ×1** (×2 in big rooms) — hidden ally. 1–2 times per game may privately **investigate** via the app (name a codename → app privately replies "crook / not crook"), then must steer the room **without revealing they're the cop**.
- **Citizens** — everyone else; win by helping the room catch the crook and by correctly matching codenames to people.

## The clue engine (the heart)
Each round, the app:
1. **Privately asks every player a question** from a leader-curated bank — get-to-know-you + values (favorite book of the Bible, oldest/youngest, a would-you-rather, a value question). Each question is tagged for "clue-ability."
2. **Leaks 1–3 anonymized clues** to the whole room — *"one of you says their favorite book is Psalms."*
3. The room **discusses out loud** and matches clue → codename → real person. Players submit **who-is-who guesses** anytime; correct matches score.

**Why the Crook is hardest:** the app leaks fewer/blander clues about the crook, and the crook can choose **silence** to starve the clue feed (but over-silence becomes a faint tell — the tension is answer-and-risk-exposure vs. stay-quiet-and-look-suspicious).

## Round + endgame flow
1. Lobby — kids join by room code, app assigns codenames + secret roles.
2. Rounds (leader sets how many — it should *take time*): private question → discuss → clues leak → who-is-who guesses → (cop may investigate).
3. **Accusation** — the room votes which codename is the Crook.
4. **Reveal** — correct → Citizens + Cop win; wrong → Crook wins. Then the app **unmasks everyone** — the big payoff, and the get-to-know-you reward (now you know who was who all along).

## Hosted-room model (Kahoot/Jackbox style)
- Leader opens the **host screen** on a projector → short room **code**.
- Kids join from phones at a short URL (no install, no login; reuse `cop_kid` name).
- **Polling, not WebSockets** — cPanel/LiteSpeed shared hosting can't run a persistent WS daemon reliably; phones poll a room-state endpoint every ~2s. Feels identical to Kahoot, rock-solid on shared hosting. Reverb stays a future drop-in behind the same controller.
- All state in the DB so a dropped phone rejoins by code and resumes.

## Content — leader-authored, I do NOT write doctrine
The question bank is leader/pastor-curated in the admin. Engine ships with a starter set of clearly-safe get-to-know-you + value questions. Questions are tagged: `clueable` (answer can become a public clue), category, and any role-flavor. (Per the standing rule, I don't invent theology — leaders own the content.)

## Data model (new tables)
- `mystery_questions` — `id, prompt, kind (getknow|value|scripture), options (json, nullable for free-text), clueable (bool), is_active, created_by`
- `game_rooms` — `id, code, host_token, status (lobby|playing|accusation|revealed|ended), round_no, rounds_total, current_question_started_at, settings (json)`
- `game_room_players` — `id, game_room_id, name, token, codename, role (crook|cop|citizen), score, alive(always true — no elimination)`
- `mystery_answers` — `id, game_room_id, round_no, player_id, question_id, answer, stayed_silent (bool)`
- `mystery_clues` — `id, game_room_id, round_no, player_id (whose answer), text, revealed_at`
- `mystery_guesses` — `id, game_room_id, guesser_player_id, target_codename, guessed_player_id, is_correct` (who-is-who)
- `mystery_investigations` — `id, game_room_id, cop_player_id, target_codename, result (bool), round_no`
- final crook accusation reuses a votes pattern (or a column on players).

Reuses existing `game_players` for identity + gentle `total_stars`.

## Routes (sketch)
- `GET /youth` — Join (enter code) / Host (admin-gated).
- `GET /youth/host` — host stage: lobby, round controls, live clue/guess board, reveal.
- `GET /youth/play/{code}` — player: join, answer/stay-silent, guess who-is-who, (cop) investigate, final accuse.
- `POST /youth/room`, `.../join`, `.../answer`, `.../guess`, `.../investigate`, `.../accuse`, host `.../advance`, poll `GET .../state`.
- `/admin/mystery` — leader curates the question bank (same pattern as `/admin/games`).

## Build phases
1. **Foundation** — migrations + models; `/admin/mystery` question-bank CRUD; seed starter questions. ← building first
2. **Room + lobby** — create room, code, join, codename assignment, role deal, lobby updates by poll.
3. **Round loop** — private question → answer/silence → clue leak → who-is-who guessing + scoring.
4. **Cop power + accusation + reveal** — investigate, final crook vote, win/lose, unmask-everyone payoff.
5. **Polish** — Considered host "stage" (big calm type), reconnection, WCAG AA, scale rules (2 crooks/cops in big rooms), stars.

## Open questions for Karlon
1. **Name** — "Undercover," "Concealed," "Hidden in Plain Sight," "The Crook," "Among the Faithful," something else?
2. **Where it lives** — its own `/youth` area (recommended; it's teen, not little-kids) vs under `/kids`.
3. **Who curates questions** — you, a youth leader, or the pastor?
4. **Group size** — typical room size? (sets when we add 2nd crook/cop and how many clues to leak.)
5. **The Cop** — keep the investigate power as designed (1–2 private "crook/not-crook" checks), or simpler/none for v1?
