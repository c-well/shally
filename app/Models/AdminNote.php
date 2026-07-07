<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminNote extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'body', 'created_by'];

    /** Body is encrypted at rest — APP_KEY holds the vault shut. */
    protected $casts = ['body' => 'encrypted'];

    public function author() { return $this->belongsTo(User::class, 'created_by'); }
}
