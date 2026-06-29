# Spec — "Spot the Counterfeit" (teen group discernment game)

Status: **scoped 2026-06-28, NOT yet shipped.** Verse Tetris is live; this is the next teen build.

## The idea in one line
A live, Kahoot-style group game for youth nights: the room is shown a set of teachings/claims — most are sound, **one is a subtle counterfeit** — and the group discusses out loud and votes to spot the false one, then sees *why* it's false. It trains discernment ("test everything; hold fast to what is good") without ever asking a human player to lie.

## Why this shape (the design conviction)
Social-deduction games (Mafia/Among Us) are addictive because of the **group arguing toward the truth**, not because a person deceives their friends. So we keep the loud-room energy and the deduction, but move the deception from *a person* to *the content*. **No player is ever assigned to lie.** The counterfeit is an idea on the screen, detected by the group. This is arguably a *more* distinctly Christian game than a baptized Mafia — it teaches detecting the lie, not telling one.

## The hosted-room model (like Kahoot / Jackbox)
- A leader opens the **host screen** on a projector/TV → gets a short **room code** (e.g. `PEACE`, 4–5 chars).
- Teens go to a short URL on their phones, enter the code + a name, and join. Their existing `cop_kid` localStorage name is reused if present.
- The **host controls the pace** (Start round → discussion timer → Lock votes → Reveal). Phones are the buzzers; the host screen is the shared stage.
- Works for a class of ~30 on the same wifi. No app install, no login.

## Round flow
1. **Show** — host screen + phones display N claims (default 4), labeled A–D. One is the counterfeit.
2. **Discuss** — a timer runs (leader-set, e.g. 60–90s). The room talks it out. (This is the whole point — the phones are quiet here.)
3. **Vote** — each player (or each **squad**, see below) taps the claim they think is false. Live tally appears on the host screen as votes land.
4. **Reveal** — the counterfeit is unmasked, with the **teaching**: why it's false and the scripture that corrects it. Points awarded.
5. Next round. A session is a set of rounds (leader picks how many).

## Two play modes (leader toggles)
- **Solo** — every phone votes; points to the individual; feeds the existing gentle `total_stars`.
- **Squad** — the room splits into teams (ties into the Squads idea from the hub plan); a squad confers and locks one answer; points to the squad; team leaderboard on the host screen. Squad mode is the louder, better youth-night experience.

## Content model — leader-authored and vetted (critical)
**I do not author doctrine.** The engine ships empty + a handful of *uncontroversial, factual* seed rounds (e.g. "'God helps those who help themselves' is **not** in the Bible"). All real content is written/approved by a youth leader or pastor in the admin.

A **round** =
- `claims`: array of statements (3–5), each `{ text, is_counterfeit: bool }` — exactly one counterfeit.
- `teaching`: why the counterfeit is false.
- `reference`: the scripture that corrects it.
- `category`: e.g. misquoted-verse, popular-myth, prosperity-twist, out-of-context.
- `difficulty`, `is_active`.

Guardrail in the admin: a round can't be saved/activated unless **exactly one** claim is flagged counterfeit and a teaching is present.

## Technical approach — polling, not WebSockets (recommended)
cPanel/LiteSpeed shared hosting doesn't reliably support a persistent WebSocket process (Reverb needs a long-running daemon + open port). So the MVP uses **short-poll**: phones poll a lightweight room-state endpoint every ~2s; the host poll is what drives the live vote tally. This is exactly how Kahoot feels to a player and is rock-solid on shared hosting. If we ever outgrow it, Reverb is a drop-in upgrade behind the same controller. **Decision: build polling first.**

State lives in the DB (a `game_rooms` row + `game_room_players` + per-round votes), so a dropped phone can rejoin with the code and resume.

## Data model (new tables)
- `discernment_rounds` — `id, category, claims (json), teaching, reference, difficulty, is_active, created_by`
- `game_rooms` — `id, code, host_token, mode (solo|squad), status (lobby|playing|revealed|ended), current_round_id, current_round_started_at, settings (json)`
- `game_room_players` — `id, game_room_id, name, token, squad, score`
- `game_room_votes` — `id, game_room_id, round_id, player_id, choice_index, is_correct`

Reuses existing `game_players` for identity + `total_stars`.

## Routes (sketch)
- `GET /youth` — landing: "Join a game" (enter code) or "Host" (leader, behind admin).
- `GET /youth/host` — host screen (room code, lobby, controls, live results). Admin-gated.
- `GET /youth/play/{code}` — player screen (join, vote, see reveal).
- `POST /youth/room` (create), `POST /youth/room/{code}/join`, `POST .../vote`, `POST .../advance` (host), `GET .../state` (poll).
- `/admin/discernment` — leader writes/vets rounds (same pattern as `/admin/games`).

## Scoring
- Correct vote = base points; faster locked votes (in solo) get a small speed bonus; squad mode splits points to the team. Completing a session grants a star or two to each player's `total_stars` (gentle, consistent with the rest of /kids).

## Build phases
1. **Engine + admin** — tables, `discernment_rounds` CRUD at `/admin/discernment` with the one-counterfeit guardrail, seed the safe examples.
2. **Host + join (lobby)** — create room, code, players join, lobby list updates by poll.
3. **Round loop** — show → discuss timer → vote → live tally → reveal + teaching. Solo mode first.
4. **Squad mode + scoring + stars**, host leaderboard.
5. **Polish** — Considered look (host screen is a "stage"; big type, calm palette), reconnection, accessibility (WCAG AA per site standard).

## Open questions for Karlon
1. **Name** — "Spot the Counterfeit"? "Test the Spirits"? "Berean"? "True / Counterfeit"?
2. **Where it lives** — under `/kids` (rename that area to something teen-appropriate?) or its own `/youth`?
3. **Who writes content** — you, a youth leader, or the pastor? (Determines how locked-down the admin is and whether we need an approval step before a round goes live.)
4. **Solo or squad first** — squad is the better room experience but a bit more to build. Start solo and add squad, or go straight to squad?
5. The optional **"advocate" debate variant** (a player is asked to make the case for a claim, not knowing if it's true) adds debate energy — but skirts the very line you flagged. Leave it out of the MVP? (My recommendation: yes, leave it out.)
