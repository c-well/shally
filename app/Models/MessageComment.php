<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageComment extends Model
{
    use SoftDeletes;

    protected $fillable = ['sermon_id', 'user_id', 'body'];

    public function user()   { return $this->belongsTo(User::class); }
    public function sermon() { return $this->belongsTo(PeaceSermon::class, 'sermon_id'); }
}
