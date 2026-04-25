<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = ['image_path', 'caption', 'subcaption', 'link_url', 'sort_order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public static function active()
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): string
    {
        return '/' . ltrim($this->image_path, '/');
    }
}
