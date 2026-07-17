<?php

namespace App\Actions\Poll;

use App\Models\Poll;

use Illuminate\Support\Facades\DB;

class UpdatePollAction
{
    public function execute(Poll $poll, array $data): Poll
    {
        return DB::transaction(function () use ($poll, $data) {
            $poll->update([
                'status' => $data['status'],
                'question' => $data['question'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            $options = $poll->options->keyBy('uuid');

            foreach ($data['options'] as $option) {
                $options->get($option['uuid'])->update([
                    'option' => $option['option'],
                ]);
            }

            return $poll;
        });
    }
}
