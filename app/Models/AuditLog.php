<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const UPDATED_AT = null; // append-only: no updates

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'details', 'ip_address'];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Write one audit entry. Never pass passwords or card data in $details.
     */
    public static function record(string $action, ?Model $subject = null, array $details = []): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'details' => $details ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }
}
