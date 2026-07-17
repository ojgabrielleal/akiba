<?php

namespace App\Actions\Locution;

use App\Models\Onair;
use App\Models\Plan;
use App\Models\Program;
use App\Models\SongRequest;

use Illuminate\Support\Facades\DB;

class FinishLocutionAction
{
    public function execute(): void
    {
        DB::transaction(function () {
            $onair = Onair::live()
                ->first();
            $auto = Program::where('execution_mode', 'auto_dj')
                ->where('is_default_auto_dj', true)
                ->first();

            if($onair) {
                if($onair->paused_plan_id) {
                    Plan::whereKey($onair->paused_plan_id)
                        ->where('status', 'paused')
                        ->update([
                            'status' => 'running',
                        ]);
                }

                $onair->update([
                    'in_air' => false,
                    'allows_song_requests' => false,
                ]);

                SongRequest::where('onair_id', $onair->id)
                    ->where('was_reproduced', false)
                    ->where('was_canceled', false)
                    ->update(['was_canceled' => true]);
            }
                    
            if ($auto) {
                $selected = collect($auto->phrases)->random();

                $phrase = [
                    'text' => $selected['text'],
                    'icon' => $selected['icon'],
                    'decoration' => $selected['decoration'],
                ];

                $auto->onair()->create([
                    'execution_mode' => 'auto_dj',
                    'phrase' => $phrase,
                ]);
            }
        });
    }
}
