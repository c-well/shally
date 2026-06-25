<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Intake engine (2026-06-25).
 *
 * One schema-driven form engine that powers every intake the church needs —
 * graduations, events, baby blessings, whatever Andre builds next. A form is a
 * definition (slug + field schema + output type + notify settings); a
 * submission is one person's answers plus any generated artifact (e.g. the
 * 1920x1080 ProPresenter PNG for graduations).
 *
 * The memorable public link is just the slug: thechurchofpeace.org/intake/{slug}.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('intake_forms', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();              // the memorable link: /intake/grad
            $t->string('title');
            $t->text('intro')->nullable();             // lede shown above the form
            $t->string('output_type', 32)->default('generic'); // graduation | event | generic
            $t->json('schema');                        // [{ key, label, type, required, options, show_if, ... }]
            $t->json('settings')->nullable();          // notify_emails[], sms_to, png opts…
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('intake_submissions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('intake_form_id')->index();
            $t->json('data');                          // { fieldKey: value, … }
            $t->string('photo_path')->nullable();      // uploaded photo (public/intake/photos)
            $t->string('output_path')->nullable();     // generated artifact (public/intake/grad)
            $t->boolean('show_text')->default(true);   // grad card: text overlay on/off
            $t->string('status', 16)->default('live'); // live | removed
            $t->string('submitter_name')->nullable();
            $t->string('submitter_email')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['intake_form_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_submissions');
        Schema::dropIfExists('intake_forms');
    }
};
