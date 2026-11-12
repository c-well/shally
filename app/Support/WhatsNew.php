<?php
namespace App\Support;

/**
 * What has been added for André, in one place.
 *
 * The admin "What's new" page and the email that announces it both read
 * this list, so a feature is written up once. Order is the order André reads
 * them in: related things together, the one to try first at the top.
 * Written for the person using the site, not for whoever built it. 'short'
 * is the one line he sees first; the rest only shows when he opens it.
 *
 * area picks the colour of the tag: music, bulletin, mail, admin.
 */
class WhatsNew
{
    public static function items(): array
    {
        return [
            [
                'key'     => 'music-day',
                'short'   => 'Singers send their tracks to you, and you see who is in.',
                'area'    => 'music',
                'tag'     => 'Music day',
                'date'    => '2026-10-21',
                'title'   => 'One link for the singers',
                'summary' => 'Make a music day, text everyone the link, and each singer sends their song: upload the track, paste a YouTube link, or say they are bringing it on CD or USB. You see everyone in running order, and can take every track home in one download, numbered for the sound desk.',
                'steps'   => [
                    'Open Admin, then Music day, and tap New music day. Pick the date. Give it a name like "The Reason We Sing" if you want it on the link preview.',
                    'Tap Text it. Your messages open with the link already written.',
                    'As songs come in, use the arrows to set the running order.',
                    'Before the service, tap Download all tracks and put them on a USB.',
                ],
                'note'    => 'YouTube links are turned into a track for you within a minute.',
                'route'   => 'admin.music',
                'cta'     => 'Open Music day',
            ],
            [
                'key'     => 'music-listen',
                'short'   => 'Check each track is the right one before the day.',
                'area'    => 'music',
                'tag'     => 'Music day',
                'date'    => '2026-10-22',
                'title'   => 'Listen to every song on the page',
                'summary' => 'Each song has its own player, so you can check a track is the right one before the day. Starting one stops the one that was playing. Singers can hear their file before they send it, and they see a bar while it uploads, so nobody sends the wrong song or gives up halfway.',
                'steps'   => [],
                'note'    => null,
                'route'   => 'admin.music',
                'cta'     => 'Open Music day',
            ],
            [
                'key'     => 'music-email',
                'short'   => 'Who sent what, on evenings songs arrive. Quiet otherwise.',
                'area'    => 'music',
                'tag'     => 'Music day',
                'date'    => '2026-11-12',
                'title'   => 'An email when songs come in',
                'summary' => 'On any evening songs arrive, you get one email at 8pm saying who sent what, and anything that needs a look. The night before a music day it sends the full running order and who you still need to chase. No songs, no email, so it stays quiet between music days.',
                'steps'   => [],
                'note'    => 'Nothing to set up. It comes to this email address.',
                'route'   => null,
            ],
            [
                'key'     => 'mail-reply',
                'short'   => 'Write back as media@ or treasurer@ without leaving the admin.',
                'area'    => 'mail',
                'tag'     => 'Mail',
                'date'    => '2026-10-14',
                'title'   => 'Reply to church email from the admin',
                'summary' => 'Open a message in the mail room, write in the box under it, and tap Send reply. It goes out from that address, like media@ or treasurer@, within a minute, and lands in the Sent folder so it shows in your other mail apps too.',
                'steps'   => [],
                'note'    => null,
                'route'   => 'admin.mail',
                'cta'     => 'Open the mail room',
            ],
            [
                'key'     => 'bulletin-normal',
                'short'   => 'Undo weeks of additions in one tap.',
                'area'    => 'bulletin',
                'tag'     => 'Bulletin',
                'date'    => '2026-10-11',
                'title'   => 'Put the bulletin back to normal',
                'summary' => 'After a few busy weeks of adding things, one tap takes the bulletin back to a typical week. You can reset just the order of service, or the order and the weekly notices together. Either one can be undone straight away.',
                'steps'   => [
                    'In the bulletin, open Bulletin tools.',
                    'Choose Reset the order of service, or Reset order and weekly notices.',
                    'When a week looks the way a normal week should, choose Save this week as normal.',
                ],
                'note'    => null,
                'url'     => '/welcome',
                'cta'     => 'Open the bulletin',
            ],
            [
                'key'     => 'admin-phone',
                'short'   => 'No pinching or sideways scrolling, on a phone or iPad.',
                'area'    => 'admin',
                'tag'     => 'Admin',
                'date'    => '2026-09-25',
                'title'   => 'The admin fits your phone',
                'summary' => 'Every admin page now fits a phone or iPad without pinching or scrolling sideways, and the lettering is clearer to read.',
                'steps'   => [],
                'note'    => null,
                'route'   => 'admin.hub',
                'cta'     => 'Open the admin',
            ],
        ];
    }

    public static function link(array $item): ?string
    {
        if (! empty($item['route'])) return route($item['route']);
        if (! empty($item['url']))   return url($item['url']);
        return null;
    }

    /** Tag colours, shared by the page and the email. [text, background] */
    public static function colours(string $area): array
    {
        return match ($area) {
            'music'    => ['#5b2a86', '#f3edf8'],
            'bulletin' => ['#03617a', '#e6f0f3'],
            'mail'     => ['#8c6f2e', '#f5eedd'],
            default    => ['#334455', '#eceef1'],
        };
    }
}
