<?php

namespace Database\Seeders;

use App\Models\GameLevel;
use Illuminate\Database\Seeder;

class KidsEscapeRoomSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: never overwrite an existing story or the staff's Live/Hidden setting.
        GameLevel::firstOrCreate(['game_type' => 'escape_room', 'reference' => 'Acts 16:25–34'], [
            'age_band' => 'teens', 'book' => 'Acts', 'title' => 'Midnight in Philippi',
            'verse_text' => 'Believe on the Lord Jesus Christ, and thou shalt be saved, and thy house.',
            'difficulty' => 2, 'config' => ['story' => 'philippi_v1'], 'is_active' => true, 'sort_order' => 1,
        ]);
    }
}
