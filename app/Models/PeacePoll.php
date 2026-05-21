<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeacePoll extends Model
{
    protected $table = 'peace_polls';

    protected $fillable = [
        'question',
        'scripture_ref',
        'scripture_explainer',
        'topic_id',
        'sermon_id',
        'is_active',
        'display_priority',
        'rotation_days',
        'active_from',
        'active_until',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'active_from'  => 'datetime',
        'active_until' => 'datetime',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(PeacePollOption::class, 'poll_id')->orderBy('display_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PeacePollResponse::class, 'poll_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(PeaceTopic::class, 'topic_id');
    }

    public function sermon(): BelongsTo
    {
        return $this->belongsTo(PeaceSermon::class, 'sermon_id');
    }

    /**
     * Resolve the best poll for a given sermon.
     *
     * Priority (matches user's "B then A" rotation requirement):
     *   1. Active poll directly bound to this sermon
     *   2. Active poll matching one of this sermon's topics
     *   3. Most recent active poll with no binding (fallback default)
     *
     * Within tier 2, falls back to display_priority then most recent.
     */
    public static function forSermon(PeaceSermon $sermon): ?self
    {
        $now = now();
        // Tier 1: sermon-bound
        $direct = self::query()
            ->where('is_active', true)
            ->where('sermon_id', $sermon->id)
            ->where(fn($q) => $q->whereNull('active_until')->orWhere('active_until', '>=', $now))
            ->orderByDesc('display_priority')
            ->orderByDesc('id')
            ->first();
        if ($direct) return $direct;

        // Tier 2: topic-matched
        $topicIds = $sermon->topics->pluck('id')->all();
        if (!empty($topicIds)) {
            $topicMatch = self::query()
                ->where('is_active', true)
                ->whereIn('topic_id', $topicIds)
                ->where(fn($q) => $q->whereNull('active_until')->orWhere('active_until', '>=', $now))
                ->orderByDesc('display_priority')
                ->orderByDesc('id')
                ->first();
            if ($topicMatch) return $topicMatch;
        }

        // Tier 3: untagged default
        return self::query()
            ->where('is_active', true)
            ->whereNull('topic_id')
            ->whereNull('sermon_id')
            ->where(fn($q) => $q->whereNull('active_until')->orWhere('active_until', '>=', $now))
            ->orderByDesc('display_priority')
            ->orderByDesc('id')
            ->first();
    }
}
