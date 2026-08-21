<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['actor', 'action', 'entity', 'entity_id', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public static function record(string $action, string $entity, ?int $entityId = null, array $metadata = []): self
    {
        return static::create([
            'actor' => auth()->user()?->email ?? 'guest',
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'metadata' => $metadata ?: null,
        ]);
    }
}
