<?php
namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

/**
 * Hub system strip endpoint. Reads the result stored nightly by
 * system:check-updates (web SAPI cannot shell out on this host).
 */
class AdminSystemController extends Controller
{
    public function updates(): JsonResponse
    {
        $raw = AppSetting::get('system_updates_json');
        if (! $raw) {
            return response()->json(['ok' => false, 'pending' => true,
                'message' => 'First check runs tonight at 5:30 AM — or ask Karlon to run: php artisan system:check-updates']);
        }
        $data = json_decode($raw, true);
        $data['checked_at_human'] = \Carbon\Carbon::parse($data['checked_at'])->diffForHumans();
        return response()->json(['ok' => true] + $data);
    }
}
