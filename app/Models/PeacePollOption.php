<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeacePollOption extends Model
{
    protected $table = 'peace_poll_options';
    protected $fillable = ['poll_id', 'option_text', 'is_canonical', 'display_order', 'response_count'];
    protected $casts = ['is_canonical' => 'boolean'];

    public function poll(): BelongsTo { return $this->belongsTo(PeacePoll::class, 'poll_id'); }
}
