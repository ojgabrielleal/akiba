<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmissionComment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'form_submission_id',
        'user_id',
        'comment',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function formSubmission()
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
