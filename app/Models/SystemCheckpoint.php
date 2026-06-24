<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemCheckpoint extends Model
{
    protected $fillable = [
        'label', 'kind', 'git_sha', 'composer_lock_path', 'db_backup_path',
        'app_version', 'created_by', 'restored_at', 'restored_by',
    ];
    protected $casts = ['restored_at' => 'datetime'];

    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function restorer() { return $this->belongsTo(User::class, 'restored_by'); }

    /** Are the backing files still present on disk (restorable)? */
    public function isRestorable(): bool
    {
        return $this->composer_lock_path && is_file($this->composer_lock_path)
            && $this->db_backup_path && is_file($this->db_backup_path);
    }
}
