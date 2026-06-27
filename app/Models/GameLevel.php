<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GameLevel extends Model
{
    protected $fillable = ['game_type','age_band','book','reference','verse_text','title','difficulty','config','is_active','created_by','sort_order'];
    protected $casts = ['config' => 'array', 'is_active' => 'boolean'];
    public function scopeActive($q) { return $q->where('is_active', true); }
    /** Key words from the verse (3+ letters, de-duped, capped) for puzzle generation. */
    public function keywords(int $max = 8): array
    {
        preg_match_all('/[A-Za-z]{3,}/', strtolower($this->verse_text), $m);
        $stop = ['the','and','for','that','but','not','his','her','him','she','they','them','thy','thou','thee','unto','with','was','are','you','your','all','have','shall','which','who','this'];
        $words = array_values(array_unique(array_diff($m[0], $stop)));
        usort($words, fn($a,$b) => strlen($b) <=> strlen($a));
        return array_slice(array_map('strtoupper', $words), 0, $max);
    }
}
