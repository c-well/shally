<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GameQuestion extends Model
{
    protected $fillable = ['topic','question','options','answer','teaching','difficulty','is_active','created_by'];
    protected $casts = ['options' => 'array', 'is_active' => 'boolean'];
    public function scopeActive($q) { return $q->where('is_active', true); }
}
