<?php

namespace App\Support;

/**
 * JSON-LD, built in PHP rather than in a Blade template.
 *
 * Laravel ships an @context Blade directive (the Context facade). A literal
 * '@context' key written inside a .blade.php file — even inside a PHP array
 * passed to json_encode — is compiled as that directive, and the key becomes
 * a block of PHP source. The JSON is then invalid and every consumer,
 * Google included, discards the whole block silently. Nothing warns you: the
 * page renders, the script tag is there, and the schema simply does not count.
 *
 * Building it here keeps the @ a character instead of a directive.
 */
class Ld
{
    /** One graph node, ready to drop inside a <script type="application/ld+json">. */
    public static function json(array $schema): string
    {
        return json_encode(
            array_merge(['@context' => 'https://schema.org'], array_filter($schema, fn ($v) => $v !== null && $v !== [])),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
