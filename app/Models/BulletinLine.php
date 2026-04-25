<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BulletinLine extends Model
{
    protected $fillable = ['bulletin_id', 'section', 'part', 'person', 'kind', 'sort_order'];
    public function bulletin() { return $this->belongsTo(Bulletin::class); }
}
