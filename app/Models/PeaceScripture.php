<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeaceScripture extends Model
{
    protected $fillable = [
        'sermon_id', 'book', 'chapter', 'verse_start', 'verse_end',
        'reference_display', 'verse_text', 'translation', 'validated', 'display_order',
    ];

    protected $casts = [
        'chapter' => 'integer', 'verse_start' => 'integer', 'verse_end' => 'integer',
        'validated' => 'boolean', 'display_order' => 'integer',
    ];

    public function sermon(): BelongsTo { return $this->belongsTo(PeaceSermon::class, 'sermon_id'); }
}
