<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnigmaGameInteraction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'enigmagame_interactions';

    public const TYPE_QUESTION = 'question';
    public const TYPE_FINAL_ANSWER = 'final_answer';

    protected $fillable = [
        'uuid',
        'enigmagame_id',
        'participant_type',
        'participant_id',
        'type',
        'content',
        'admin_response',
        'result',
        'responded_by',
        'responded_at',
        'response_notified_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'response_notified_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function enigmagame()
    {
        return $this->belongsTo(EnigmaGame::class, 'enigmagame_id');
    }

    public function participant()
    {
        return $this->morphTo();
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
