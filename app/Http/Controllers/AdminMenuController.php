<?php
namespace App\Http\Controllers;

use App\Services\MenuConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The menu studio: pick a template, reorder/rename/hide items, autosaves. */
class AdminMenuController extends Controller
{
    public function show(): View
    {
        return view('admin.menu', [
            'config'      => MenuConfig::get(),
            'recommended' => MenuConfig::recommendedConfig(),
            'defaultCfg'  => MenuConfig::defaultConfig(),
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'style'                    => 'required|in:' . implode(',', MenuConfig::STYLES),
            'groups'                   => 'required|array|max:12',
            'groups.*.label'           => 'nullable|string|max:40',
            'groups.*.collapsible'     => 'boolean',
            'groups.*.items'           => 'array|max:20',
            'groups.*.items.*.label'   => 'required|string|max:48',
            'groups.*.items.*.route'   => 'nullable|string|max:64',
            'groups.*.items.*.url'     => 'nullable|url|max:300',
            'groups.*.items.*.badge'   => 'nullable|string|max:12',
            'groups.*.items.*.hidden'  => 'boolean',
            'groups.*.items.*.external'=> 'boolean',
        ]);
        MenuConfig::save($data);
        return response()->json(['ok' => true]);
    }
}
