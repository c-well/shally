<?php
namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * The menu engine (Karlon 2026-07-06): the public nav is DATA, not markup.
 * One JSON config (AppSetting `menu_config`) + four render styles in
 * partials/menu-nav.blade.php + an admin studio at /admin/menu.
 *
 * Styles: classic (collapsible sections, the original look) · grouped
 * (flat butter rows under quiet labels) · tiles (Sabbath-first cards) ·
 * today (living day card + rows).
 *
 * Item keys are route NAMES (resolved safely at render; unknown routes are
 * skipped, so a renamed route can never 500 the menu). `url` items allow
 * only http(s). The `bulletin` item auto-targets the v2 editor for clerks.
 *
 * Evidence backing the "recommended" preset (interaction_events, Jun 6–Jul 5):
 * only ~12% of visitors open the menu; Prayer/Hour-of-Prayer is the most
 * touched thing sitewide; Spiritual Life is the most-expanded section.
 */
class MenuConfig
{
    public const STYLES = ['classic', 'grouped', 'tiles', 'today', 'clean'];

    public static function get(): array
    {
        $raw = AppSetting::get('menu_config');
        $cfg = $raw ? json_decode($raw, true) : null;
        if (! is_array($cfg) || empty($cfg['groups'])) $cfg = self::defaultConfig();
        $cfg['style'] = in_array($cfg['style'] ?? '', self::STYLES, true) ? $cfg['style'] : 'classic';
        return $cfg;
    }

    public static function save(array $cfg): void
    {
        AppSetting::set('menu_config', json_encode($cfg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /** Resolve an item to a real href, or null to skip it. */
    public static function href(array $item): ?string
    {
        if (! empty($item['url'])) {
            return preg_match('~^https?://~i', $item['url']) ? $item['url'] : null;
        }
        $route = $item['route'] ?? null;
        if ($route === 'bulletin.smart') {   // clerk-aware bulletin target
            return (auth()->check()
                && in_array(auth()->user()->role, ['super_admin', 'clerk'], true)
                && AppSetting::get('bulletin_editor', 'v1') === 'v2')
                ? route('admin.bulletin') : url('/welcome');
        }
        if ($route === 'home') return url('/');
        return ($route && RouteFacade::has($route)) ? route($route) : null;
    }

    /** Mirrors the pre-engine menu exactly — deploying changes nothing visually. */
    public static function defaultConfig(): array
    {
        return [
            'style'  => 'classic',
            'groups' => [
                ['label' => null, 'collapsible' => false, 'items' => [
                    ['label' => 'Home', 'route' => 'home'],
                ]],
                ['label' => 'About Us', 'collapsible' => true, 'items' => [
                    ['label' => 'Our story', 'route' => 'about'],
                    ['label' => 'Beliefs', 'route' => 'beliefs'],
                    ['label' => 'Contact', 'route' => 'contact.show'],
                ]],
                ['label' => null, 'collapsible' => false, 'items' => [
                    ['label' => 'Visit Us', 'route' => 'visit'],
                    ['label' => 'Prayer Request', 'route' => 'prayer.show'],
                ]],
                ['label' => 'Spiritual Life', 'collapsible' => true, 'items' => [
                    ['label' => 'Bible (KJV & ESV)', 'route' => 'bible'],
                    ['label' => 'Hymnal', 'route' => 'hymnal'],
                    ['label' => 'Peace Notes', 'route' => 'peace-notes'],
                    ['label' => 'Messages', 'route' => 'messages'],
                    ['label' => 'Scripture Games', 'route' => 'kids'],
                    ['label' => 'Undercover (Youth)', 'route' => 'youth'],
                    ['label' => 'Sabbath School Lesson', 'route' => 'lesson.show'],
                ]],
                ['label' => null, 'collapsible' => false, 'items' => [
                    ['label' => 'Bulletin', 'route' => 'bulletin.smart'],
                    ['label' => 'Announcements', 'route' => 'announcements'],
                    ['label' => 'Donate', 'url' => 'https://adventistgiving.org/#/org/AN48SH/envelope/start', 'external' => true],
                ]],
            ],
        ];
    }

    /** The evidence-based layout: Prayer elevated, submenus flattened, Calendar debuted. */
    public static function recommendedConfig(): array
    {
        return [
            'style'  => 'grouped',
            'groups' => [
                ['label' => 'This Sabbath', 'collapsible' => false, 'items' => [
                    ['label' => 'Bulletin', 'route' => 'bulletin.smart'],
                    ['label' => 'Announcements', 'route' => 'announcements'],
                    ['label' => 'Calendar', 'route' => 'calendar', 'badge' => 'NEW'],
                    ['label' => 'Messages', 'route' => 'messages'],
                ]],
                ['label' => 'Prayer', 'collapsible' => false, 'items' => [
                    ['label' => 'Send a Prayer Request', 'route' => 'prayer.show'],
                ]],
                ['label' => 'Worship & Study', 'collapsible' => false, 'items' => [
                    ['label' => 'Sabbath School Lesson', 'route' => 'lesson.show'],
                    ['label' => 'Bible (KJV & ESV)', 'route' => 'bible'],
                    ['label' => 'Hymnal', 'route' => 'hymnal'],
                    ['label' => 'Peace Notes', 'route' => 'peace-notes'],
                    ['label' => 'Scripture Games', 'route' => 'kids'],
                    ['label' => 'Undercover (Youth)', 'route' => 'youth'],
                ]],
                ['label' => 'Our Church', 'collapsible' => false, 'items' => [
                    ['label' => 'Visit Us', 'route' => 'visit'],
                    ['label' => 'Our Story', 'route' => 'about'],
                    ['label' => 'Beliefs', 'route' => 'beliefs'],
                    ['label' => 'Contact', 'route' => 'contact.show'],
                    ['label' => 'Give', 'url' => 'https://adventistgiving.org/#/org/AN48SH/envelope/start', 'external' => true],
                ]],
            ],
        ];
    }
}
