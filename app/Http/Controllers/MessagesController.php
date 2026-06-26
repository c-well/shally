<?php
namespace App\Http\Controllers;

use App\Models\PeaceSermon;
use Illuminate\View\View;

/**
 * Public "Messages" — the church's sermon audio, built on the Find Peace audio
 * pipeline (PeaceSermon). A clean listenable archive: newest featured, the rest
 * below, each with an on-brand audio player that loads only when played.
 */
class MessagesController extends Controller
{
    public function index(): View
    {
        $messages = PeaceSermon::whereNotNull('published_at')
            ->whereNotNull('audio_url')
            ->where('audio_status', '!=', 'failed')
            ->orderByDesc('sermon_date')->orderByDesc('id')
            ->get();

        return view('messages.index', ['messages' => $messages]);
    }
}
