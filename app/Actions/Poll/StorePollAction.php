<?php

namespace App\Actions\Poll;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StorePollAction
{
    public function execute(User $user, array $data): Poll
    {
        return DB::transaction(function () use ($user, $data) {
            $poll = Poll::create([
                'user_id' => $user->id,
                'status' => $data['status'],
                'question' => $data['question'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            $poll->options()->createMany(
                collect($data['options'])
                    ->map(fn (array $option) => ['option' => $option['option']])
                    ->all()
            );

            return $poll;
        });
    }
}
