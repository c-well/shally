<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeacePollResponse extends Model
{
    protected $table = 'peace_poll_responses';
    public $timestamps = false;
    protected $fillable = ['poll_id', 'option_id', 'ip_hash', 'ua_hash', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function poll(): BelongsTo { return $this->belongsTo(PeacePoll::class, 'poll_id'); }
    public function option(): BelongsTo { return $this->belongsTo(PeacePollOption::class, 'option_id'); }
}
