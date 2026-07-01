<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One person's answers to an intake form, plus any generated artifact.
 */
class IntakeSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'intake_form_id', 'data', 'photo_path', 'photo_original_path', 'output_path', 'show_text',
        'status', 'submitter_name', 'submitter_email', 'ip',
    ];

    protected $casts = [
        'data'      => 'array',
        'show_text' => 'boolean',
    ];

    // In-memory defaults so a freshly-created model reflects the DB defaults
    // before it's reloaded (otherwise show_text reads null → false).
    protected $attributes = [
        'show_text' => true,
        'status'    => 'live',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(IntakeForm::class, 'intake_form_id');
    }

    public function value(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /** Best human label for this submission. */
    public function displayName(): string
    {
        return $this->submitter_name
            ?: ($this->value('name') ?: ('Submission #' . $this->id));
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? '/' . ltrim($this->photo_path, '/') : null;
    }

    public function outputUrl(): ?string
    {
        return $this->output_path ? '/' . ltrim($this->output_path, '/') : null;
    }

    /** The pristine uploaded photo (falls back to the working copy). Used by the editor. */
    public function originalUrl(): ?string
    {
        $p = $this->photo_original_path ?: $this->photo_path;
        return $p ? '/' . ltrim($p, '/') : null;
    }
}
