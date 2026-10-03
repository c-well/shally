<?php

namespace App\Services;

class KidsVerseMission
{
    public static function forReference(string $reference): array
    {
        return match ($reference) {
            'John 3:16' => [
                'title' => 'Love that gives',
                'notes' => ['God loves the world—including you.', 'God showed his love by giving his Son, Jesus.', 'This verse invites us to trust Jesus and receive life with him.'],
                'practice' => 'Show love today by giving someone your time or help.',
                'reflect' => 'How did God show his love for the world?',
                'answer' => 'He gave his Son, Jesus. Love is more than a feeling; it gives.',
            ],
            'Matthew 11:28' => [
                'title' => 'A place to rest',
                'notes' => ['Jesus invites tired and burdened people to come to him.', '“Heavy laden” means carrying a heavy load.', 'You can bring your worries to Jesus in prayer.'],
                'practice' => 'Take a quiet moment to tell Jesus one thing that is worrying you.',
                'reflect' => 'Who does Jesus invite to come to him?',
                'answer' => 'People who are tired or carrying heavy burdens. He promises rest.',
            ],
            'John 14:6' => [
                'title' => 'Follow the way',
                'notes' => ['Jesus calls himself the way, the truth, and the life.', 'Jesus helps us know what God is like.', 'Jesus invites us to come to the Father through him.'],
                'practice' => 'Choose one way to follow Jesus today: tell the truth, forgive, or help.',
                'reflect' => 'What three things does Jesus call himself?',
                'answer' => 'The way, the truth, and the life.',
            ],
            'Philippians 4:13' => [
                'title' => 'Strength for today',
                'notes' => ['Paul learned to be content in plenty and in need.', 'Christ gave Paul strength through changing circumstances.', 'We can ask Christ for strength to be faithful when life is hard.'],
                'practice' => 'Ask Jesus for strength to do one kind or right thing that feels difficult.',
                'reflect' => 'Who gives Paul strength in both easy and difficult times?',
                'answer' => 'Christ. This passage is about depending on him in every circumstance.',
            ],
            default => [
                'title' => 'Discover the verse',
                'notes' => ['Build the verse one phrase at a time.', 'Notice what this verse says about God.', 'Read the whole verse and choose a word to remember.'],
                'practice' => 'Share one thing you noticed in this verse with your family.',
                'reflect' => 'Which word or phrase will you remember?',
                'answer' => 'Read the verse together and talk about why that part stood out to you.',
            ],
        };
    }

    public static function letterSteps(string $verse): array
    {
        $steps = [];
        foreach (preg_split('/\s+/u', trim($verse), -1, PREG_SPLIT_NO_EMPTY) as $index => $word) {
            $letters = preg_split('//u', preg_replace('/[^\p{L}\p{N}]/u', '', $word), -1, PREG_SPLIT_NO_EMPTY);
            $chunks = array_chunk($letters, 4);
            foreach ($chunks as $chunkIndex => $chunk) {
                $steps[] = ['word_index' => $index, 'letters' => implode('', $chunk), 'finishes_word' => $chunkIndex === count($chunks) - 1];
            }
        }

        return $steps;
    }

    public static function phrases(string $verse): array
    {
        return array_map(fn ($words) => implode(' ', $words), array_chunk(preg_split('/\s+/u', trim($verse), -1, PREG_SPLIT_NO_EMPTY), 4));
    }
}
