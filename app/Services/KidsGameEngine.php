<?php

namespace App\Services;

use App\Models\GameLevel;
use InvalidArgumentException;

/** Checks puzzle moves on the server. Activity remains evidence, never identity proof. */
class KidsGameEngine
{
    public function challenge(GameLevel $level): array
    {
        $words = preg_split('/\s+/u', trim(preg_replace('/[.;,:!?]/u', '', $level->verse_text)), -1, PREG_SPLIT_NO_EMPTY);
        if (! $words) {
            throw new InvalidArgumentException('This verse needs some words.');
        }
        if ($level->game_type === 'hidden_words') {
            return ['words' => $words, 'total' => count($words)];
        }
        if ($level->game_type === 'verse_tetris') {
            return ['total' => count($words)];
        }
        $keys = $level->keywords();
        if ($level->game_type === 'memory_match') {
            $keys = array_slice($keys, 0, $level->age_band === 'little' ? 5 : 6);
            if (! $keys) {
                throw new InvalidArgumentException('This verse needs a word with at least three letters.');
            }
            $cards = array_merge($keys, $keys);
            shuffle($cards);

            return ['cards' => $cards, 'total' => count($keys)];
        }
        $size = $level->age_band === 'teens' ? 13 : 11;
        $keys = array_values(array_filter($keys, fn ($w) => strlen($w) <= $size));
        if (! $keys) {
            throw new InvalidArgumentException('This verse has no words that fit the puzzle.');
        }
        $grid = array_fill(0, $size, array_fill(0, $size, null));
        $directions = $level->age_band === 'teens' ? [[0, 1], [1, 0], [1, 1], [-1, 1], [0, -1], [1, -1]] : [[0, 1], [1, 0], [1, 1]];
        $positions = [];
        $failed = false;
        foreach ($keys as $word) {
            $placed = false;
            for ($attempt = 0; $attempt < 250; $attempt++) {
                [$dr, $dc] = $directions[array_rand($directions)];
                $r = random_int(0, $size - 1);
                $c = random_int(0, $size - 1);
                $len = strlen($word);
                $er = $r + $dr * ($len - 1);
                $ec = $c + $dc * ($len - 1);
                if (min($er, $ec) < 0 || max($er, $ec) >= $size) {
                    continue;
                }
                for ($i = 0; $i < $len; $i++) {
                    if ($grid[$r + $dr * $i][$c + $dc * $i] !== null && $grid[$r + $dr * $i][$c + $dc * $i] !== $word[$i]) {
                        continue 2;
                    }
                }
                for ($i = 0; $i < $len; $i++) {
                    $grid[$r + $dr * $i][$c + $dc * $i] = $word[$i];
                }
                $positions[$word] = [[$r, $c], [$er, $ec]];
                $placed = true;
                break;
            }
            if (! $placed) {
                $failed = true;
                break;
            }
        }
        // Guaranteed fallback: never silently omit a target word.
        if ($failed) {
            $grid = array_fill(0, $size, array_fill(0, $size, null));
            $positions = [];
            $rows = range(0, $size - 1);
            shuffle($rows);
            foreach ($keys as $i => $word) {
                $r = $rows[$i];
                $c = random_int(0, $size - strlen($word));
                foreach (str_split($word) as $j => $letter) {
                    $grid[$r][$c + $j] = $letter;
                }
                $positions[$word] = [[$r, $c], [$r, $c + strlen($word) - 1]];
            }
        }
        foreach ($grid as &$row) {
            foreach ($row as &$letter) {
                if ($letter === null) {
                    $letter = chr(random_int(65, 90));
                }
            }
        }

        return ['grid' => $grid, 'words' => $keys, 'positions' => $positions, 'total' => count($keys)];
    }

    public function move(string $type, array $challenge, array $state, array $event): array
    {
        $state += ['progress' => 0, 'score' => 0];
        switch ($type) {
            case 'hidden_words':
                $index = $event['index'] ?? null;
                if (($event['type'] ?? '') !== 'reveal' || ! is_int($index) || $index !== $state['progress'] || $index >= $challenge['total']) {
                    $this->invalid();
                }
                $state['progress']++;
                break;
            case 'memory_match':
                $index = $event['index'] ?? null;
                $matched = $state['matched'] ?? [];
                if (($event['type'] ?? '') !== 'flip' || ! is_int($index) || ! isset($challenge['cards'][$index]) || in_array($index, $matched, true) || ($state['first'] ?? null) === $index) {
                    $this->invalid();
                }
                if (($state['first'] ?? null) === null) {
                    $state['first'] = $index;
                } else {
                    $first = $state['first'];
                    $state['first'] = null;
                    if ($challenge['cards'][$first] === $challenge['cards'][$index]) {
                        $matched[] = $first;
                        $matched[] = $index;
                        $state['matched'] = $matched;
                        $state['progress'] = intdiv(count($matched), 2);
                    }
                }
                break;
            case 'word_search':
                $a = $event['start'] ?? [];
                $b = $event['end'] ?? [];
                $size = count($challenge['grid']);
                if (($event['type'] ?? '') !== 'word' || count($a) !== 2 || count($b) !== 2) {
                    $this->invalid();
                }
                foreach (array_merge($a, $b) as $n) {
                    if (! is_int($n) || $n < 0 || $n >= $size) {
                        $this->invalid();
                    }
                }
                [$r,$c] = $a;
                [$er,$ec] = $b;
                if ($r !== $er && $c !== $ec && abs($er - $r) !== abs($ec - $c)) {
                    $this->invalid();
                }
                $dr = $er <=> $r;
                $dc = $ec <=> $c;
                $length = max(abs($er - $r), abs($ec - $c)) + 1;
                $word = '';
                for ($i = 0; $i < $length; $i++) {
                    $word .= $challenge['grid'][$r + $dr * $i][$c + $dc * $i];
                }
                if (! in_array($word, $challenge['words'], true)) {
                    $word = strrev($word);
                }
                $found = $state['found'] ?? [];
                if (! in_array($word, $challenge['words'], true) || in_array($word, $found, true)) {
                    $this->invalid();
                }
                $found[] = $word;
                $state['found'] = $found;
                $state['progress'] = count($found);
                break;
            case 'verse_tetris':
                // Tetris line clears are client-reported, with physical bounds, not a board replay.
                $lines = $event['lines'] ?? null;
                if (($event['type'] ?? '') !== 'piece' || ! is_int($lines) || $lines < 0 || $lines > 4) {
                    $this->invalid();
                }
                $pieces = ($state['pieces'] ?? 0) + 1;
                $totalLines = ($state['lines'] ?? 0) + $lines;
                if ($totalLines * 10 > $pieces * 4) {
                    $this->invalid();
                }
                $state['pieces'] = $pieces;
                $state['lines'] = $totalLines;
                $state['score'] += [0, 40, 100, 300, 1200][$lines];
                $state['progress'] = min($challenge['total'], $totalLines);
                break;
            default: $this->invalid();
        }
        if ($type !== 'verse_tetris') {
            $state['score'] = $state['progress'];
        }
        $state['completed'] = $state['progress'] === $challenge['total'];

        return $state;
    }

    private function invalid(): never
    {
        throw new InvalidArgumentException('That move did not match this game.');
    }
}
