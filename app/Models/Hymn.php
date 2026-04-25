<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Hymn extends Model
{
    protected $primaryKey = 'number';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['number', 'title'];
}
