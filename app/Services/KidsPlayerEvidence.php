<?php

namespace App\Services;

class KidsPlayerEvidence
{
    public static function assess(string $review, int $qualified, int $invalid, int $actions, int $runs): array
    {
        return match (true) {
            $review === 'known' => ['Known to the team', 'A staff member marked this player as someone they know.'],
            $review === 'test' => ['Test account', 'Marked as testing; excluded from public celebrations and rewards.'],
            $review === 'exclude' => ['Excluded', 'Hidden from public celebrations by a staff member.'],
            $invalid >= 5 && $invalid >= $actions => ['Needs review', 'Repeated moves did not match the game. This may be a client issue or automation.'],
            $qualified > 0 => ['Likely player', 'Valid game activity spread over time. This suggests a person playing, but does not prove identity.'],
            default => ['Not enough evidence', $runs > 0 ? 'Some activity is recorded, but not enough to assess. Short or fast games are normal.' : 'Historical nickname only; no sessions measured since the update.'],
        };
    }
}
