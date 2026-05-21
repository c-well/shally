<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeaceQaPair extends Model
{
    protected $fillable = ['sermon_id', 'question', 'answer', 'display_order', 'confidence_score'];
    protected $casts = ['confidence_score' => 'float', 'display_order' => 'integer'];

    public function sermon(): BelongsTo { return $this->belongsTo(PeaceSermon::class, 'sermon_id'); }
}
