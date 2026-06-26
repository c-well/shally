<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A schema-driven intake form. The schema is an ordered list of field
 * definitions; the public page renders them and applies show_if conditions so
 * the form stays tucked away until the leading question is answered.
 */
class IntakeForm extends Model
{
    protected $fillable = [
        'slug', 'title', 'intro', 'output_type', 'schema', 'settings', 'is_active', 'created_by',
    ];

    protected $casts = [
        'schema'    => 'array',
        'settings'  => 'array',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(IntakeSubmission::class);
    }

    public function liveSubmissions(): HasMany
    {
        return $this->submissions()->where('status', 'live')->latest('id');
    }

    /** @return array<int,array<string,mixed>> the field definitions */
    public function fields(): array
    {
        return $this->schema['fields'] ?? [];
    }

    public function field(string $key): ?array
    {
        foreach ($this->fields() as $f) {
            if (($f['key'] ?? null) === $key) return $f;
        }
        return null;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /* ── site-menu opt-in (Andre pushes a form to the public nav) ── */

    public function inMenu(): bool
    {
        return (bool) $this->setting('in_menu', false);
    }

    public function menuLabel(): string
    {
        return $this->setting('menu_label') ?: $this->title;
    }

    /** Active forms the admin has pushed to the public menu. */
    public static function menuForms()
    {
        return static::where('is_active', true)->orderBy('id')->get()
            ->filter(fn ($f) => $f->inMenu())->values();
    }
}
