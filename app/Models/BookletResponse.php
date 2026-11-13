<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One person's "Your Sabbath" answers from the web booklet. Read only in the admin. */
class BookletResponse extends Model
{
    protected $fillable = ['bulletin_id', 'picks', 'story', 'first_name', 'ip_hash'];

    protected $casts = ['picks' => 'array'];

    public function bulletin(): BelongsTo { return $this->belongsTo(Bulletin::class); }
}
