<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestFollowup extends Model
{
    protected $fillable = ['guest_id', 'kind', 'due_on', 'status', 'channel', 'body', 'sent_at'];
    protected $casts = ['due_on' => 'date', 'sent_at' => 'datetime'];

    public function guest() { return $this->belongsTo(Guest::class); }

    /** The engine's default wording — used when body is null. Warm, short, human. */
    public function defaultBody(): string
    {
        $n = $this->guest->firstName();
        return match ($this->kind) {
            'thanks'    => "Hi {$n}, thank you for worshiping with us at Shalom yesterday — it was a joy to have you. We'd love to see you again. God bless! — The Church of Peace",
            'questions' => "Hi {$n}! It's the Shalom family. Two quick things: was there anything you liked (or didn't) about your visit? And would you like updates about prayer meetings & announcements? Just reply — a real person reads these.",
            'birthday'  => "Happy birthday, {$n}! The Shalom family is celebrating you today. \"The Lord bless thee, and keep thee.\" — The Church of Peace",
            default     => (string) $this->body,
        };
    }
}
