<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Event;
use Illuminate\View\View;

/**
 * The clerk's events manager — a dedicated, effortless place to add events.
 *
 * Adding is just name + date (+ an optional flyer); it autosaves and goes live
 * the moment the basics are filled in. Everything mutates through the existing
 * EventController AJAX endpoints (store/update/flyer/destroy/restore), so this
 * is purely a nicer front door onto the same data the home page shows.
 */
class AdminEventsController extends Controller
{
    public function index(): View
    {
        // Clerk sees everything upcoming — including hidden (is_public=false)
        // events, so she can put them back on the site.
        $upcoming = Event::with('department')
            ->whereDate('start_at', '>=', today()->subDay())
            ->orderBy('start_at')->get();

        $past = Event::with('department')
            ->whereDate('start_at', '<', today()->subDay())
            ->orderByDesc('start_at')->limit(12)->get();

        $departments = Department::orderBy('name')->get();

        return view('admin.events', compact('upcoming', 'past', 'departments'));
    }
}
