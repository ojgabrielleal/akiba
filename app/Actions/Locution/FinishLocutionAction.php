<?php

namespace App\Actions\Locution;

use App\Models\Onair;
use App\Models\Program;
use App\Models\SongRequest;

use Illuminate\Support\Facades\DB;

class FinishLocutionAction
{
    public function execute(): void
    {
        DB::transaction(function () {
            $onair = $this->liveOnair();
            $auto = $this->defaultAutoDj();

            if ($onair) {
                $this->finishOnair($onair);
            }

            if ($auto) {
                $this->startAutoDj($auto);
            }
        });
    }

    private function liveOnair(): ?Onair
    {
        return Onair::live()->first();
    }

    private function defaultAutoDj(): ?Program
    {
        return Program::where('execution_mode', 'auto_dj')
            ->where('is_default_auto_dj', true)
            ->first();
    }

    private function finishOnair(Onair $onair): void
    {
        $onair->update([
            'in_air' => false,
            'allows_song_requests' => false,
        ]);

        SongRequest::where('onair_id', $onair->id)
            ->where('was_reproduced', false)
            ->where('was_canceled', false)
            ->update(['was_canceled' => true]);
    }

    private function startAutoDj(Program $auto): void
    {
        $auto->onair()->create([
            'execution_mode' => 'auto_dj',
            'phrase' => $this->randomPhrase($auto),
        ]);
    }

    private function randomPhrase(Program $auto): array
    {
        $selected = collect($auto->phrases)->random();

        return [
            'text' => $selected['text'],
            'icon' => $selected['icon'],
            'decoration' => $selected['decoration'],
        ];
    }
}
