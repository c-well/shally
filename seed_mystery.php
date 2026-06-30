<?php
// Starter question bank for "Undercover". Clearly-safe, uncontroversial
// get-to-know-you + value + gentle scripture prompts. Leaders grow the set.
// Run: php artisan tinker --execute="require '/home/shalom/seed_mystery.php';"  (or include via a runner)

use App\Models\MysteryQuestion;

$starter = [
    // get-to-know-you (strong clue material)
    ['getknow', 'Where do you fall in your family?', ['Oldest', 'Middle', 'Youngest', 'Only child']],
    ['getknow', 'Which is closer to you?', ['Morning person', 'Night owl']],
    ['getknow', 'Pick your place', ['Mountains', 'Ocean', 'City', 'Countryside']],
    ['getknow', 'Your go-to food', ['Pizza', 'Tacos', 'Burgers', 'Wings']],
    ['getknow', 'Pick a season', ['Spring', 'Summer', 'Fall', 'Winter']],
    ['getknow', 'How many people live in your house?', ['1–2', '3–4', '5–6', '7 or more']],
    ['getknow', 'On a free Saturday you would rather', ['Be with a big group', 'Hang with one or two friends', 'Have time to yourself']],

    // value / would-you-rather
    ['value', 'You would rather be known as', ['Kind', 'Honest', 'Brave', 'Wise']],
    ['value', 'What matters more to you right now?', ['Being understood', 'Being respected']],
    ['value', 'In a group you usually', ['Lead the way', 'Support the leader', 'Keep the peace']],
    ['value', 'You would rather', ['Speak up first', 'Listen, then speak']],

    // scripture / faith (gentle, non-doctrinal preference questions)
    ['scripture', 'Which book draws you most right now?', ['Psalms', 'Proverbs', 'John', 'Revelation']],
    ['scripture', 'A name for God that means the most to you', ['Shepherd', 'Father', 'Friend', 'King']],
    ['scripture', 'Which feels most like you lately?', ['Learning to trust', 'Learning to wait', 'Learning to forgive', 'Learning to be brave']],
];

$made = 0;
foreach ($starter as [$kind, $prompt, $options]) {
    if (MysteryQuestion::where('prompt', $prompt)->exists()) continue;
    MysteryQuestion::create([
        'prompt'    => $prompt,
        'kind'      => $kind,
        'options'   => $options,
        'clueable'  => true,
        'is_active' => true,
    ]);
    $made++;
}

echo "seeded {$made} mystery questions (total now " . MysteryQuestion::count() . ")\n";
