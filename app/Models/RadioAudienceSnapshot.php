<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioAudienceSnapshot extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'radio_station_id',
        'listeners',
        'status',
        'response_time_ms',
    ];

    protected $casts = [
        'listeners' => 'integer',
        'response_time_ms' => 'integer',
    ];

    protected $hidden = [
        'radio_station_id',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function radioStation()
    {
        return $this->belongsTo(RadioStation::class);
    }
}
