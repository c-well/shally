<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A question the "Undercover" mystery asks players privately. A clueable answer
 * may surface to the room (anonymized) as a clue to who is who. Leaders own the
 * bank — the app never invents the questions, and none of them ask a player to
 * deceive anyone.
 */
class MysteryQuestion extends Model
{
    protected $fillable = ['prompt', 'kind', 'options', 'clueable', 'is_active', 'created_by'];

    protected $casts = [
        'options'   => 'array',
        'clueable'  => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
