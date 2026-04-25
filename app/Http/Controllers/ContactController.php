<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Public contact form for the landing page.
 *
 * Replaces the WordPress /contact/ page on thechurchofpeace.org so visitors
 * never have to leave our site. Mail goes to the church address with the
 * mandatory CC to contact@c-wellpics.com (per project mail policy).
 *
 * Spam defense: honeypot field + per-IP rate limit (set on the route).
 * No CAPTCHA — the WordPress form's math captcha was annoying and the
 * honeypot+throttle combo blocks the same automated bots in practice.
 */
class ContactController extends Controller
{
    /** GET /contact — render form */
    public function show(): View
    {
        return view('contact', [
            'sent' => session('sent', false),
            'page' => \App\Models\Page::bySlug('contact'),
        ]);
    }

    /** POST /contact — validate, dispatch mail, redirect with flash */
    public function send(Request $request): RedirectResponse
    {
        // Honeypot: a hidden field bots fill in. Real users leave it empty.
        if (filled($request->input('website'))) {
            return back()->with('sent', true);   // pretend success, don't tell the bot
        }

        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:200',
            'message' => 'required|string|max:5000',
        ]);

        $body  = "From: {$data['name']} <{$data['email']}>\n";
        $body .= "Sent via the public contact form on thechurchofpeace.org\n";
        $body .= "IP: " . $request->ip() . " · UA: " . substr($request->userAgent() ?? '', 0, 200) . "\n";
        $body .= str_repeat('-', 60) . "\n\n";
        $body .= $data['message'];

        Mail::raw($body, function ($m) use ($data) {
            $m->to('contact@thechurchofpeace.org')
              ->cc('contact@c-wellpics.com')
              ->replyTo($data['email'], $data['name'])
              ->subject('Contact form — ' . $data['name']);
        });

        return redirect()->route('contact.show')->with('sent', true);
    }
}
