<?php

namespace App\Services;

use InvalidArgumentException;

/** A puzzle adaptation of Acts 16:25–34; Scripture and fictional mechanics stay distinct. */
class KidsEscapeRoom
{
    public static function rooms(): array
    {
        return [
            [
                'title' => 'The midnight song', 'place' => 'The inner prison', 'art' => 'prison-v1.jpg',
                'intro' => 'Philippi. Midnight. Paul and Silas are in prison, yet a song rises in the darkness. Search the room for three pieces of their story.',
                'goal' => 'Find 3 clues. Restore the midnight verse.', 'reference' => 'Acts 16:25',
                'verse' => 'And at midnight Paul and Silas prayed, and sang praises unto God: and the prisoners heard them.',
                'puzzle' => 'Restore the verse', 'instruction' => 'Tap the fragments in the order of the passage. Tap a placed fragment to take it back.',
                'options' => ['and the prisoners heard them.', 'and sang praises unto God:', 'And at midnight', 'prayed,', 'Paul and Silas'],
                'hints' => ['Begin with the time of night, then the two names.', 'After their names comes prayer, then praise, then the people who listened.'],
                'spots' => [
                    ['id' => 'window', 'name' => 'Moonlit window', 'x' => 15, 'y' => 22, 'title' => 'When hope sounds unlikely', 'text' => 'The night is far from over. The passage begins: “And at midnight…”', 'fragment' => 'And at midnight'],
                    ['id' => 'scroll', 'name' => 'Rolled parchment', 'x' => 18, 'y' => 60, 'title' => 'Two voices', 'text' => 'Paul and Silas prayed, then sang praises to God. Their circumstances had not yet changed.', 'fragment' => 'Paul and Silas prayed, and sang praises unto God:'],
                    ['id' => 'bench', 'name' => 'Prison bench', 'x' => 87, 'y' => 66, 'title' => 'Someone is listening', 'text' => 'Their worship was heard by the other prisoners. Faith can encourage people nearby.', 'fragment' => 'and the prisoners heard them.'],
                ],
                'reveal' => 'A great earthquake shakes the prison. The doors open and everyone’s chains come loose. Acts 16:26 describes God’s deliverance; solving this puzzle is our way of exploring the story.',
            ],
            [
                'title' => 'The open door', 'place' => 'The prison threshold', 'art' => 'door-v1.jpg',
                'intro' => 'The door stands open. The frightened jailer thinks everyone has gone. Paul stays and calls out to reassure him.',
                'goal' => 'Find 3 clues. Send Paul’s reassuring message.', 'reference' => 'Acts 16:28–29',
                'verse' => '“for we are all here.” — Acts 16:28 (excerpt). “Then he called for a light…” — Acts 16:29 (excerpt).',
                'puzzle' => 'Light the message', 'instruction' => 'Turn each word dial to rebuild Paul’s message. Then send the signal.',
                'dials' => [['THEY', 'WE', 'YOU'], ['WERE', 'WILL', 'ARE'], ['SOME', 'ALL', 'NONE'], ['GONE', 'HERE', 'LOST']],
                'hints' => ['Look at the passage in your journal. Who is still in the prison?', 'The four words are WE · ARE · ALL · HERE. Turn the dials to match.'],
                'spots' => [
                    ['id' => 'door', 'name' => 'Open doorway', 'x' => 65, 'y' => 45, 'title' => 'An unexpected choice', 'text' => 'An open door does not mean Paul abandons the jailer. He speaks so the jailer knows the prisoners are still there.', 'fragment' => 'we are'],
                    ['id' => 'chains', 'name' => 'Loose chains', 'x' => 62, 'y' => 86, 'title' => 'No one missing', 'text' => 'The chains are loose, but Paul’s message says ALL of them are still HERE.', 'fragment' => 'all here'],
                    ['id' => 'lamp', 'name' => 'Oil lamp', 'x' => 10, 'y' => 57, 'title' => 'Bring a light', 'text' => 'The jailer calls for a light and comes inside. A reassuring word changes what happens next.', 'fragment' => 'Then he called for a light'],
                ],
                'reveal' => 'The jailer brings Paul and Silas out and asks about salvation. They point him to Jesus and speak God’s word to everyone in his household. Acts 16:30–32.',
            ],
            [
                'title' => 'A new beginning', 'place' => 'The jailer’s home', 'art' => 'home-v1.jpg',
                'intro' => 'The story moves from fear to hospitality. Explore the table and discover what the household hears and does.',
                'goal' => 'Find 3 objects. Connect each one to the passage.', 'reference' => 'Acts 16:32–34',
                'verse' => '“they spake unto him the word of the Lord” (v.32) · “washed their stripes” (v.33) · “he set meat before them, and rejoiced” (v.34).',
                'puzzle' => 'Make room for hope', 'instruction' => 'Choose an object, then choose the action it represents. You can use a keyboard or tap—no dragging needed.',
                'items' => ['bread' => 'Shared bread', 'scroll' => 'Scripture scroll', 'water' => 'Basin & cloth'],
                'targets' => ['hear' => 'Hear God’s word', 'care' => 'Care for their wounds', 'share' => 'Share a meal'],
                'hints' => ['Match what each object is used for with what the household does.', 'Scroll → hear. Basin and cloth → care. Bread → share.'],
                'spots' => [
                    ['id' => 'water', 'name' => 'Basin & cloth', 'x' => 18, 'y' => 61, 'title' => 'Care becomes practical', 'text' => 'The jailer washes their wounds. “Stripes” in this passage means the injuries from their beating. You do not see those injuries in this game.', 'fragment' => 'washed their stripes'],
                    ['id' => 'scroll', 'name' => 'Scripture scroll', 'x' => 49, 'y' => 61, 'title' => 'Everyone can listen', 'text' => 'Paul and Silas speak the word of the Lord to the jailer and everyone in his house. They hear the message about Jesus.', 'fragment' => 'the word of the Lord'],
                    ['id' => 'bread', 'name' => 'Shared bread', 'x' => 83, 'y' => 65, 'title' => 'A table, not a cell', 'text' => 'The jailer gives them a meal and rejoices with his household. “Meat” here means food.', 'fragment' => 'he set meat before them, and rejoiced'],
                ],
                'reveal' => 'They stayed. They shared hope. A household heard God’s word and responded with faith and care.',
            ],
        ];
    }

    public static function move(array $state, array $event): array
    {
        $room = $state['progress'] ?? 0;
        $rooms = self::rooms();
        if (($event['room'] ?? null) !== $room || ! isset($rooms[$room])) {
            throw new InvalidArgumentException('Reopen this room to continue.');
        }
        $state += ['discovered' => [], 'placed' => [], 'attempts' => 0];
        $state['feedback'] = '';
        if (($event['type'] ?? '') === 'inspect') {
            $object = $event['object'] ?? null;
            $allowed = array_column($rooms[$room]['spots'], 'id');
            if (! is_string($object) || ! in_array($object, $allowed, true)) {
                throw new InvalidArgumentException('Choose an object in this room.');
            }
            $key = $room.':'.$object;
            if (! in_array($key, $state['discovered'], true)) {
                $state['discovered'][] = $key;
            }
            return $state;
        }
        if (($event['type'] ?? '') !== 'solve') {
            throw new InvalidArgumentException('Choose a puzzle action.');
        }
        foreach ($rooms[$room]['spots'] as $spot) {
            if (! in_array($room.':'.$spot['id'], $state['discovered'], true)) {
                $state['feedback'] = 'Explore all three objects first. Each holds part of the story.';
                return $state;
            }
        }
        $state['attempts']++;
        if ($room === 2) {
            $matches = ['scroll' => 'hear', 'water' => 'care', 'bread' => 'share'];
            $item = $event['item'] ?? null;
            if (! is_string($item) || ! isset($matches[$item]) || ! in_array($event['target'] ?? null, array_values($matches), true)) {
                throw new InvalidArgumentException('Choose an object and an action.');
            }
            if ($matches[$item] !== $event['target']) {
                $state['feedback'] = 'Look at the passage again. What was this object used for?';
                return $state;
            }
            $state['placed'][$item] = $event['target'];
            $correct = count($state['placed']) === 3;
            $state['feedback'] = $correct ? '' : 'Connected. Find the next act of kindness.';
        } else {
            $answer = $event['answer'] ?? null;
            $correct = $answer === ($room === 0 ? [2, 4, 3, 1, 0] : ['WE', 'ARE', 'ALL', 'HERE']);
            if (! $correct) {
                $state['feedback'] = $room === 0 ? 'Not quite in order. Follow the passage from midnight to the listening prisoners.' : 'Read Paul’s words in your journal, then turn the dials again.';
            }
        }
        if ($correct) {
            $state['progress']++;
            $state['feedback'] = '';
        }
        return $state;
    }
}
