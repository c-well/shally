<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 80)->unique();          // 'about', 'visit', 'beliefs', etc.
            $t->string('title', 200);                  // Editable title shown in <h1>
            $t->string('eyebrow', 120)->nullable();    // Small uppercase label above h1
            $t->text('body_md');                       // Source-of-truth markdown
            $t->text('body_html')->nullable();         // Cached rendered HTML (for fast page loads)
            $t->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        // Seed the existing pages with their current content so nothing's lost
        \DB::table('pages')->insert([
            [
                'slug' => 'about',
                'title' => 'Our story.',
                'eyebrow' => 'Shalom SDA · since the Bronx, NY',
                'body_md' => "A community of believers gathering each Sabbath under one banner — peace. *Shalom* is the Hebrew word for it; the Church of Peace is what we live toward.\n\n## Who we are.\n\nShalom Seventh-day Adventist Church has worshipped at 3323 White Plains Road for decades. We're part of the worldwide Seventh-day Adventist family — a Bible-believing, Sabbath-keeping community focused on the soon return of Jesus Christ.\n\nWhat sets us apart isn't a creed; it's a culture. Quiet, hospitable, multigenerational. The grandmothers sit beside the new visitors. The children sing without being asked twice. Andre opens the doors before sunrise on Mondays for the prayer meeting — and means it.\n\n> \"The peace of God, which surpasses all understanding, will guard your hearts and your minds in Christ Jesus.\"\n> — Philippians 4:7\n\n## What you'll find here.\n\nSabbath School at 9:30 AM, where we open the Bible together study guide in hand. Worship at 11:00 AM — hymns, prayer, the Word preached plainly. A potluck most Sabbaths after service. Bible class at 4 PM on Zoom. Hour of Prayer at 5 AM weekdays for the early risers. Wednesday night prayer meeting at 7.\n\nIf you're new, you don't have to be anyone or know anyone. Just walk in.\n\n## What we believe.\n\nShalom holds to the 28 Fundamental Beliefs of the Seventh-day Adventist Church — the centrality of Scripture, the saving work of Jesus, the seventh-day Sabbath, and the blessed hope of Christ's soon return.\n\n[Read our core beliefs →](/beliefs)",
                'body_html' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
