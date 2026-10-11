<?php
/**
 * Anthropic API pricing — USD per million tokens.
 * Update this file when Anthropic announces new prices.
 * Source: https://www.anthropic.com/pricing  (verify before relying on these for billing decisions)
 *
 * Last updated: 2026-10-11
 *
 * Two things were wrong before this revision and both inflated the dashboard:
 *
 *   1. No current model was listed, so every call fell through to the Sonnet
 *      'default' below. A Haiku call was billed on the dashboard at roughly
 *      thirty times what it actually cost.
 *   2. Opus 4.7 was priced at $15/$75. It is $5/$25. Historical
 *      feedback.assistant rows are overstated about threefold.
 *
 * Rows written before 2026-10-11 therefore read high. They were not re-costed,
 * because the token counts are the real record and the dollar column is
 * derived — recomputing it would rewrite history that nobody disputed.
 */
return [
    'as_of'   => '2026-10-11',
    // Cost-spike circuit breaker.
    // If ANY single Anthropic call exceeds this many USD, an immediate alert
    // email fires + audit log entry + red banner on /admin for 24h.
    // $1.00 is still well clear of normal use: the Peace generator on Opus 5.5
    // runs about $0.12 a sermon, the feedback reply well under a cent, and the
    // event parser on Haiku 5.5 about two hundredths of a cent.
    'alert_threshold_usd' => 1.00,

    'models' => [
        // model_id  =>  [input, cache_read, cache_write, output]   (per million tokens)

        // ── current ──────────────────────────────────────────────────────
        // Haiku 5.5 is tiered: these are the rates for prompts up to 100K
        // tokens. Over 100K it is $0.50 / $2.50 with cache read $0.05 and
        // cache write $0.625. Everything here sends a few hundred tokens, so
        // the cheap tier always applies; if that ever stops being true this
        // table needs a tier concept rather than a single row.
        'claude-haiku-5-5'   => ['input' => 0.10,  'cache_read' => 0.01,  'cache_write' => 0.125, 'output' => 0.50],
        'claude-sonnet-5-5'  => ['input' => 2.00,  'cache_read' => 0.20,  'cache_write' => 2.50,  'output' => 10.00],
        'claude-sonnet-5'    => ['input' => 2.00,  'cache_read' => 0.20,  'cache_write' => 2.50,  'output' => 10.00],
        'claude-opus-5-5'    => ['input' => 4.00,  'cache_read' => 0.20,  'cache_write' => 5.00,  'output' => 20.00],
        'claude-opus-5'      => ['input' => 5.00,  'cache_read' => 0.50,  'cache_write' => 6.25,  'output' => 25.00],

        // ── previous generation, kept so old rows still cost correctly ────
        'claude-opus-4-8'    => ['input' => 5.00,  'cache_read' => 0.50,  'cache_write' => 6.25,  'output' => 25.00],
        'claude-opus-4-7'    => ['input' => 5.00,  'cache_read' => 0.50,  'cache_write' => 6.25,  'output' => 25.00],
        'claude-opus-4-6'    => ['input' => 5.00,  'cache_read' => 0.50,  'cache_write' => 6.25,  'output' => 25.00],
        'claude-sonnet-4-6'  => ['input' => 3.00,  'cache_read' => 0.30,  'cache_write' => 3.75,  'output' => 15.00],
        'claude-sonnet-4-5'  => ['input' => 3.00,  'cache_read' => 0.30,  'cache_write' => 3.75,  'output' => 15.00],
        'claude-opus-4-5'    => ['input' => 15.00, 'cache_read' => 1.50,  'cache_write' => 18.75, 'output' => 75.00],
        'claude-haiku-4-5'   => ['input' => 1.00,  'cache_read' => 0.10,  'cache_write' => 1.25,  'output' => 5.00],
        // The dated form of the same model — the event parser used to send it.
        'claude-haiku-4-5-20251001' => ['input' => 1.00, 'cache_read' => 0.10, 'cache_write' => 1.25, 'output' => 5.00],
    ],

    // Fallback when a model is not listed. Deliberately Sonnet-rate: high
    // enough that an unlisted model shows up as a real number worth chasing,
    // rather than disappearing into rounding. If a row on the dashboard is
    // priced at exactly Sonnet rates for a model that is not Sonnet, that is
    // the signal this table needs the missing entry.
    'default' => ['input' => 3.00, 'cache_read' => 0.30, 'cache_write' => 3.75, 'output' => 15.00],
];
