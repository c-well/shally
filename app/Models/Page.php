<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Environment\Environment;

/**
 * A site page with editable markdown body.
 *
 * Body is stored as markdown ({@see body_md}) — the source of truth — and a
 * rendered HTML copy is cached in {@see body_html} so public-page loads don't
 * pay the markdown parse cost on every request. The HTML cache is regenerated
 * automatically on every save by an Eloquent observer.
 */
use App\Concerns\HasRevisions;

class Page extends Model
{
    use HasRevisions;

    /* ── revision history (see App\Concerns\HasRevisions) ── */
    public function revisionFields(): array { return ['title', 'eyebrow', 'body_md']; }
    public function revisionLabel(): string { return 'Page · /' . $this->slug; }


    protected $fillable = ['slug', 'title', 'eyebrow', 'body_md', 'body_html', 'updated_by_user_id'];

    /** Auto-regenerate the HTML cache whenever the markdown source changes. */
    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if ($page->isDirty('body_md')) {
                $page->body_html = static::renderMarkdown($page->body_md);
            }
        });
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by_user_id');
    }

    /**
     * Render the markdown to HTML. Called from the Eloquent saving observer
     * (registered in {@see \App\Providers\AppServiceProvider::boot()}) so the
     * cache is always in sync with the source.
     *
     * GFM extension gives us tables, autolinks, strikethrough, and task lists
     * out of the box — useful for André when he wants to add a "what to bring"
     * checklist to the Visit page or similar.
     */
    public static function renderMarkdown(string $md): string
    {
        $env = new Environment([
            'html_input'         => 'allow',   // we trust admin-authored content
            'allow_unsafe_links' => false,     // still strip javascript: URLs as defense in depth
            'max_nesting_level'  => 20,
        ]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new GithubFlavoredMarkdownExtension());

        return (new MarkdownConverter($env))->convert($md)->getContent();
    }

    /** Convenience: load by slug, returning null if missing (so views can fallback). */
    public static function bySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}
