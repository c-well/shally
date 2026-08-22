<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A HANDOUT is a page you hand someone — a gift registry, a welcome card for a
 * visitor, a one-off event, a notice. It is deliberately NOT part of the site:
 * no nav entry, no sitemap row, noindex + noarchive on the response. You reach
 * it with the token you were given, and it is built to stop existing.
 *
 * WHY IT IS NOT A CALENDAR EVENT: the calendar answers "when do things
 * happen." A handout answers "here, take this." Merging them puts a baby
 * registry in the church diary, where nobody would think to look for it, and
 * leaves it there forever after the baby is born.
 *
 * ── THE ONE RULE: nothing lives here by default. ──
 * Every handout either dies on a date ('expires') or nags the person who made
 * it, on a fixed heartbeat, until they kill it ('open'). There is no third
 * mode, and 'open' is NOT "permanent" — it is "not dead yet, and still being
 * asked about." That is the whole point of the feature: the failure mode of a
 * church website is that a temporary thing quietly becomes a permanent one
 * nobody remembers owning. An expiry date or a recurring nudge is what stops
 * the site from silting up. If you ever add a "never expires" option, you have
 * removed the reason this table exists.
 */
class Handout extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'token', 'template', 'title', 'eyebrow', 'body', 'link_url', 'link_label',
        'image_path', 'theme', 'happens_at', 'location', 'mode', 'expires_at',
        'nudge_every_days', 'nudged_at', 'created_by',
    ];

    protected $casts = [
        'happens_at'  => 'datetime',
        'expires_at'  => 'datetime',
        'nudged_at'   => 'datetime',
        'last_seen_at'=> 'datetime',
    ];

    public const TZ = 'America/New_York';

    /**
     * The wizard's four starting points. Each is a shape, not a rigid form —
     * the clerk can still edit every field afterwards. `asks` drives which
     * questions step 2 shows, so a registry never asks for a location and an
     * event never asks for a shop link.
     */
    public const TEMPLATES = [
        'registry' => [
            'name'    => 'Registry',
            'blurb'   => 'A gift list — baby, wedding, housewarming. One button straight to it.',
            'eyebrow' => 'With love from the family',
            'label'   => 'View the registry',
            'theme'   => 'mothers',
            'asks'    => ['title', 'body', 'link'],
            'days'    => 120,
        ],
        'guest' => [
            'name'    => 'Guest welcome',
            'blurb'   => 'For someone who visited — service times, where we are, how to reach us.',
            'eyebrow' => 'We are glad you came',
            'label'   => 'Say hello',
            'theme'   => 'default',
            'asks'    => ['title', 'body', 'link', 'location'],
            'days'    => 60,
        ],
        'event' => [
            'name'    => 'One-off event',
            'blurb'   => 'A single date — concert, outreach, program. Sign-ups and views tracked.',
            'eyebrow' => 'You are invited',
            'label'   => 'Let us know you are coming',
            'theme'   => 'easter',
            'asks'    => ['title', 'body', 'link', 'when', 'location'],
            'days'    => 14,
        ],
        'notice' => [
            'name'    => 'Notice',
            'blurb'   => 'Something the church should know, with a link if there is one.',
            'eyebrow' => 'A word from the church',
            'label'   => 'Read more',
            'theme'   => 'default',
            'asks'    => ['title', 'body', 'link'],
            'days'    => 30,
        ],
    ];

    /** Moods the card can wear, so twelve handouts don't look like one form. */
    public const THEMES = [
        'default'      => 'Teal (house)',
        'mothers'      => 'Rose',
        'easter'       => 'Green',
        'communion'    => 'Violet',
        'thanksgiving' => 'Amber',
        'christmas'    => 'Deep red',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits()
    {
        return $this->hasMany(HandoutVisit::class);
    }

    /**
     * Token alphabet drops 0/O/1/l/i — these get read off a printed QR card and
     * typed by hand often enough that ambiguous glyphs are a real support cost.
     */
    public static function mintToken(): string
    {
        $alphabet = '23456789abcdefghjkmnpqrstuvwxyz';
        do {
            $t = '';
            for ($i = 0; $i < 10; $i++) {
                $t .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::withTrashed()->where('token', $t)->exists());

        return $t;
    }

    public function isExpired(): bool
    {
        return $this->mode === 'expires'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /** Live = reachable by anyone holding the link right now. */
    public function isLive(): bool
    {
        return ! $this->trashed() && ! $this->isExpired();
    }

    public function url(): string
    {
        return route('handout.show', $this->token);
    }

    /** Human lifespan line for the admin list — never a bare timestamp. */
    public function lifespanLabel(): string
    {
        if ($this->trashed()) {
            return 'Destroyed';
        }
        if ($this->mode === 'open') {
            return 'Open · nudges every ' . $this->nudge_every_days . ' days';
        }
        if (! $this->expires_at) {
            return 'No date set';
        }
        if ($this->isExpired()) {
            return 'Expired ' . $this->expires_at->timezone(self::TZ)->diffForHumans();
        }

        $days = (int) now()->startOfDay()->diffInDays($this->expires_at->copy()->startOfDay(), false);

        return match (true) {
            $days === 0 => 'Gone tonight',
            $days === 1 => 'Gone tomorrow',
            $days <= 14 => "Gone in {$days} days",
            default     => 'Until ' . $this->expires_at->timezone(self::TZ)->format('M j'),
        };
    }

    /**
     * An 'open' handout is due a nudge every nudge_every_days, counted from the
     * last nudge (or from creation, for one that has never been nudged).
     */
    public function isNudgeDue(): bool
    {
        if ($this->mode !== 'open' || $this->trashed()) {
            return false;
        }
        $since = $this->nudged_at ?? $this->created_at;

        return $since === null || $since->addDays($this->nudge_every_days)->isPast();
    }

    /** Days this handout has been alive — the number that makes a nudge bite. */
    public function ageInDays(): int
    {
        return (int) ($this->created_at?->diffInDays(now()) ?? 0);
    }

    public function templateMeta(): array
    {
        return self::TEMPLATES[$this->template] ?? self::TEMPLATES['notice'];
    }
}
