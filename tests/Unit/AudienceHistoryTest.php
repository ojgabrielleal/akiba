<?php

namespace Tests\Unit;

use App\Http\Resources\AudienceResource;
use App\Models\RadioAudienceSnapshot;
use App\Models\RadioStation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tests\TestCase;

class AudienceHistoryTest extends TestCase
{
    public function test_week_shows_monday_to_friday_with_daily_totals(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(12, 0));

        try {
            $station = new RadioStation(['name' => 'Rádio Teste']);
            $station->setRelation('audienceSnapshots', new Collection([
                (new RadioAudienceSnapshot)->forceFill(['listeners' => 10, 'created_at' => '2026-10-12 01:00:00']),
                (new RadioAudienceSnapshot)->forceFill(['listeners' => 25, 'created_at' => '2026-10-12 23:59:00']),
                (new RadioAudienceSnapshot)->forceFill(['listeners' => 0, 'created_at' => '2026-10-13 10:00:00']),
                (new RadioAudienceSnapshot)->forceFill(['listeners' => 99, 'created_at' => '2026-10-11 10:00:00']),
            ]));

            $result = AudienceResource::toCollectionArray(
                collect([$station]),
                Request::create('/', 'GET', ['audience_period' => 'week']),
                'history',
            );

            $this->assertSame(['seg 12/10', 'ter 13/10', 'qua 14/10', 'qui 15/10', 'sex 16/10'], $result['labels']);
            $this->assertSame([35, 0, null, null, null], $result['series'][0]['data']);

            $this->travelTo(now()->setDate(2026, 10, 18));
            $weekend = AudienceResource::toCollectionArray(
                collect([$station]),
                Request::create('/', 'GET', ['audience_period' => 'week']),
                'history',
            );
            $this->assertSame($result['labels'], $weekend['labels']);
            $this->assertSame($result['series'][0]['data'], $weekend['series'][0]['data']);
        } finally {
            $this->travelBack();
        }
    }
}
