<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulletinLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['bulletin_id', 'section', 'part', 'person', 'kind', 'sort_order'];
    public function bulletin() { return $this->belongsTo(Bulletin::class); }
}
