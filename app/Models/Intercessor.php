<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Intercessor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'pin_hash', 'role',
        'added_by_intercessor_id', 'added_by_user_id',
        'last_seen_at', 'last_ip', 'active',
        'pin_wrong_count', 'pin_locked_until',
    ];

    protected $casts = [
        'active'           => 'boolean',
        'last_seen_at'     => 'datetime',
        'pin_locked_until' => 'datetime',
    ];

    protected $hidden = ['pin_hash'];

    public function sessions(): HasMany { return $this->hasMany(IntercessorSession::class); }

    public function isHead(): bool { return $this->role === 'head'; }

    public function checkPin(string $pin): bool {
        return Hash::check($pin, $this->pin_hash);
    }

    /** E.164-ish tidy for Twilio ("+1..."). Assumes US if 10 digits. */
    public static function normalizePhone(string $raw): string {
        $d = preg_replace('/\D+/', '', $raw);
        if (strlen($d) === 10) return '+1' . $d;
        if (strlen($d) === 11 && $d[0] === '1') return '+' . $d;
        return str_starts_with($d, '+') ? $d : '+' . $d;
    }
}
