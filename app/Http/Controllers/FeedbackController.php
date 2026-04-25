<?php
namespace App\Http\Controllers;

use App\Models\FeedbackTicket;
use App\Models\User;
use App\Services\ClaudeAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    private const LOG_PATH = '/home/shalom/feedback-log.md';
    private const TAGS     = ['bug', 'idea', 'question', 'note'];

    public function index(Request $request): View
    {
        $user = $request->user();
        // Andre sees his own tickets; super_admins see everyone's (so Karlon can review)
        $isAdmin = $user && in_array($user->role, ['clerk', 'super_admin'], true);

        $base = FeedbackTicket::with(['user:id,name', 'closer:id,name'])->orderByDesc('created_at');
        if (! $isAdmin) $base->where('user_id', $user->id);

        $pending = (clone $base)->pending()->get();
        $closed  = (clone $base)->closed()->where('closed_at', '>=', now()->subDays(14))->get();

        return view('feedback', [
            'tags'        => self::TAGS,
            'claudeReply' => $request->session()->get('claudeReply'),
            'pending'     => $pending,
            'closed'      => $closed,
            'isAdmin'     => $isAdmin,
        ]);
    }

    public function store(Request $request, ClaudeAssistant $claude): RedirectResponse
    {
        $data = $request->validate([
            "tag"             => "required|string|in:" . implode(",", self::TAGS),
            "body"            => "required|string|max:2000",
            "attachments"     => "nullable|array|max:5",
            "attachments.*"   => "file|max:10240|extensions:jpg,jpeg,png,webp,gif",
        ]);

        $user      = $request->user();
        $userName  = $user?->name ?? "unknown";
        $timestamp = now()->format("Y-m-d H:i T");
        $body      = trim($data["body"]);

        // 1) Append to markdown log (write-behind audit trail)
        $entry = sprintf("## %s · %s · %s\n\n%s\n\n", $timestamp, $userName, strtoupper($data["tag"]), $body);
        @file_put_contents(self::LOG_PATH, $entry, FILE_APPEND | LOCK_EX);

        // 2) Ask Claude for an immediate reply
        $reply = $claude->reply($userName, $data["tag"], $body);

        // 3) Create the ticket row
        $ticket = FeedbackTicket::create([
            "user_id"      => $user->id,
            "tag"          => $data["tag"],
            "message"      => $body,
            "claude_reply" => $reply,
            "status"       => "pending",
        ]);

        // 4) Process attachments — normalize via GD (max 1600w, JPEG q82)
        if ($request->hasFile("attachments")) {
            $manifest = [];
            $dir = storage_path("app/feedback-attachments/" . $ticket->id);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);

            foreach ($request->file("attachments") as $i => $file) {
                $original = $file->getClientOriginalName();
                $img = $this->openImageGd($file->getRealPath(), strtolower($file->getClientOriginalExtension()));
                if (!$img) continue;

                // Resize if wider than 1600px (preserve aspect)
                $w = imagesx($img); $h = imagesy($img);
                if ($w > 1600) {
                    $newH = (int) round($h * 1600 / $w);
                    $resized = imagecreatetruecolor(1600, $newH);
                    imagecopyresampled($resized, $img, 0, 0, 0, 0, 1600, $newH, $w, $h);
                    imagedestroy($img);
                    $img = $resized;
                }

                $name = sprintf("%d_%s.jpg", $i, \Illuminate\Support\Str::random(8));
                $path = $dir . "/" . $name;
                imagejpeg($img, $path, 82);
                imagedestroy($img);

                $manifest[] = [
                    "file"     => $name,
                    "original" => mb_substr($original, 0, 200),
                    "size"     => filesize($path),
                    "uploaded" => now()->toIso8601String(),
                ];
            }
            if ($manifest) {
                $ticket->attachments = $manifest;
                $ticket->save();
            }
        }

        // 5) Mirror Claude\x27s reply to the log
        $replyBlock = $reply
            ? sprintf("> **Claude replied:**\n> %s\n\n---\n\n", str_replace("\n", "\n> ", $reply))
            : "---\n\n";
        @file_put_contents(self::LOG_PATH, $replyBlock, FILE_APPEND | LOCK_EX);

        return redirect()->route("feedback.index")
            ->with("status", "sent")
            ->with("claudeReply", $reply)
            ->with("ticket_id", $ticket->id);
    }

    /** Auth-gated attachment serve — owner or admin only. */
    public function attachment(Request $request, FeedbackTicket $ticket, int $idx)
    {
        $user = $request->user();
        $isAdmin = $user && in_array($user->role, ["clerk", "super_admin"], true);
        if (!$user || (!$isAdmin && $ticket->user_id !== $user->id)) abort(403);

        $manifest = $ticket->attachments ?? [];
        if (!isset($manifest[$idx])) abort(404);
        $file = $manifest[$idx]["file"] ?? null;
        if (!$file || !preg_match("/^[a-zA-Z0-9_]+\.jpg$/", $file)) abort(404);

        $path = storage_path("app/feedback-attachments/" . $ticket->id . "/" . $file);
        if (!is_file($path)) abort(404);

        return response()->file($path, [
            "Content-Type"  => "image/jpeg",
            "Cache-Control" => "private, max-age=86400",
        ]);
    }

    private function openImageGd(string $path, string $ext): \GdImage|false
    {
        return match ($ext) {
            "jpg", "jpeg" => @imagecreatefromjpeg($path),
            "png"         => @imagecreatefrompng($path),
            "webp"        => @imagecreatefromwebp($path),
            "gif"         => @imagecreatefromgif($path),
            default       => false,
        };
    }

    /** PATCH /feedback/{ticket}/close — admin action. */
    public function close(Request $request, FeedbackTicket $ticket): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['clerk', 'super_admin'], true)) {
            return response()->json(['error' => 'forbidden'], 403);
        }
        $data = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);
        $ticket->markClosed($user, $data['note'] ?? null);
        return response()->json(['ok' => true, 'ticket' => $ticket->fresh()]);
    }

    /** PATCH /feedback/{ticket}/reopen — admin undo. */
    public function reopen(Request $request, FeedbackTicket $ticket): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['clerk', 'super_admin'], true)) {
            return response()->json(['error' => 'forbidden'], 403);
        }
        $ticket->markReopened();
        return response()->json(['ok' => true, 'ticket' => $ticket->fresh()]);
    }
}
