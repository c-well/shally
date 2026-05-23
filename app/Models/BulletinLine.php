<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulletinLine extends Model
{
    use SoftDeletes;

    protected $fillable = ['bulletin_id', 'section', 'part', 'person', 'kind', 'sort_order'];
    public function bulletin() { return $this->belongsTo(Bulletin::class); }

    /**
     * SECTION_OF_TRUTH guard (2026-05-23, option B).
     *
     * `section` is the title of a section_header row. It must NEVER appear on
     * a kind=line row — that's what created the phantom-header bug Andre fought
     * for weeks. This boot hook scrubs section on save if the row is a line,
     * defense-in-depth in case future code (or a stray import, or a copy-paste
     * mistake in a controller) tries to set it. The controller's loadStandardOrder
     * is already correct; this is the belt-and-suspenders backstop.
     */
    protected static function booted(): void
    {
        static::saving(function (BulletinLine $line) {
            if (($line->kind ?? 'line') === 'line') {
                $line->section = null;
            }
        });
    }
}
