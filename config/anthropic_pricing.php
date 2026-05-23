<?php
/**
 * Anthropic API pricing — USD per million tokens.
 * Update this file when Anthropic announces new prices.
 * Source: https://www.anthropic.com/pricing  (verify before relying on these for billing decisions)
 *
 * Last updated: 2026-05-23
 */
return [
    'as_of'   => '2026-05-23',
    // Cost-spike circuit breaker.
    // If ANY single Anthropic call exceeds this many USD, an immediate alert
    // email fires + audit log entry + red banner on /admin for 24h.
    // Default $1.00 — well above normal Sonnet sermon cost (~$0.07).
    // A $1 single-call spend signals a runaway loop, accidental Opus usage,
    // or a massive context that shouldn'\''t have been sent.
    'alert_threshold_usd' => 1.00,

    'models' => [
        // model_id  =>  [input, cached_input, cache_write, output]   (per million tokens)
        'claude-sonnet-4-5'        => ['input' => 3.00,  'cache_read' => 0.30,  'cache_write' => 3.75,  'output' => 15.00],
        'claude-sonnet-4-6'        => ['input' => 3.00,  'cache_read' => 0.30,  'cache_write' => 3.75,  'output' => 15.00],
        'claude-opus-4-5'          => ['input' => 15.00, 'cache_read' => 1.50,  'cache_write' => 18.75, 'output' => 75.00],
        'claude-opus-4-6'          => ['input' => 15.00, 'cache_read' => 1.50,  'cache_write' => 18.75, 'output' => 75.00],
        'claude-opus-4-7'          => ['input' => 15.00, 'cache_read' => 1.50,  'cache_write' => 18.75, 'output' => 75.00],
        'claude-haiku-4-5'         => ['input' => 1.00,  'cache_read' => 0.10,  'cache_write' => 1.25,  'output' => 5.00],
    ],
    // Fallback if model isn't in the list (uses Sonnet pricing — moderate estimate)
    'default' => ['input' => 3.00, 'cache_read' => 0.30, 'cache_write' => 3.75, 'output' => 15.00],
];
