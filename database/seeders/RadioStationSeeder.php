<?php

namespace Database\Seeders;

use App\Models\RadioStation;
use Illuminate\Database\Seeder;

class RadioStationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            [
                'name' => 'Rádio Akiba',
                'logo' => '/img/brand/logo.webp',
                'website' => config('app.url'),
                'endpoint' => config('services.stream.metadata')
                    ?? 'https://cast.radioamc.com.br/api-json/Vkc1d2FrMHdNVUpRVkRBOStS',
                'listeners_path' => 'ouvintes_conectados',
            ],
            [
                'name' => 'Rádio Doru',
                'logo' => 'https://www.radiodoru.com.br/admin/assets/img/logo.png',
                'website' => 'https://www.radiodoru.com.br/',
                'endpoint' => 'https://paineldj6.com.br:20038/status-json.xsl',
                'listeners_path' => 'icestats.source.0.listeners',
            ],
            [
                'name' => 'Rádio Mirai',
                'logo' => 'https://img.radios.com.br/radio/lg/radio190637_1741332317.png',
                'website' => 'https://www.miraionline.com.br/',
                'endpoint' => 'https://stm6.xradios.com.br:6902/stats?json=1',
                'listeners_path' => 'currentlisteners',
            ],
            [
                'name' => 'Rádio Nikkey',
                'logo' => 'https://img.radios.com.br/radio/md/radio72519_1529518559.png',
                'website' => 'http://radioetvnikkey.com.br/',
                'endpoint' => 'https://stm8.voxhd.com.br:7996/stats?json=1',
                'listeners_path' => 'currentlisteners',
            ],
            [
                'name' => 'Rádio J-Hero',
                'logo' => 'https://radiojhero.com/assets/logo.png',
                'website' => 'https://radiojhero.com/',
                'endpoint' => 'https://api.radiojhero.com/streaming/np',
                'listeners_path' => 'listeners.current',
            ],
            [
                'name' => 'Rádio Anime Night',
                'logo' => 'https://www.animenight.com.br/social/animenight.jpg',
                'website' => 'http://www.animenight.com.br/',
                'endpoint' => 'https://stm16.voxhd.com.br:10374/stats?json=1',
                'listeners_path' => 'currentlisteners',
            ],
            [
                'name' => 'Rádio Toku Hero Club',
                'logo' => 'https://img.radios.com.br/radio/lg/radio145648_1756463989.jpeg',
                'website' => 'https://www.radiotokuheroclub.com/',
                'endpoint' => 'http://sv12.hdradios.net:7664/stats?json=1',
                'listeners_path' => 'currentlisteners',
            ],
            [
                'name' => 'Rádio Animu',
                'logo' => 'https://www.animu.moe/wp-content/uploads/2021/04/Imagem-SEO-2021.png',
                'website' => 'https://www.animu.com.br/',
                'endpoint' => 'https://api.animu.com.br/',
                'listeners_path' => 'listeners',
            ],
            [
                'name' => 'Rádio Tokyo Groove FM',
                'logo' => 'https://app.player-webservic.com/radio-dashboard/model1/uploads/sites/8fb6aad34a28cd6783f89f8e9b9433dc/site_logo_1783024012.png',
                'website' => 'https://tokyogroovefm.com/home.php',
                'endpoint' => 'https://app.player-webservic.com/site4/ajax/get_player_nowplaying.php?hash=8fb6aad34a28cd6783f89f8e9b9433dc',
                'listeners_path' => 'data.listeners.current',
            ],
            [
                'name' => 'Rede Blast',
                'logo' => 'https://redeblast.com/assets/blast/img/default.png',
                'website' => 'https://redeblast.com/',
                'endpoint' => 'https://centova4.transmissaodigital.com:20143/stats?json=1',
                'listeners_path' => 'currentlisteners',
            ],
            [
                'name' => 'Rádio Okinawa',
                'logo' => 'https://img.radios.com.br/radio/lg/radio204874_1661782384.png',
                'website' => 'https://radiookinawa.minharadioonline.net/',
                'endpoint' => 'https://hts05.brascast.com:9080/status-json.xsl',
                'listeners_path' => 'icestats.source.0.listeners',
            ],
            [
                'name' => 'Rádio P6 PopAsia',
                'logo' => 'https://img.radios.com.br/radio/lg/radio236180_1762420981.jpeg',
                'website' => 'https://rmaisbr.com/channel/radiok/',
                'endpoint' => 'https://ec5.yesstreaming.net:1430/status-json.xsl',
                'listeners_path' => 'icestats.source.listeners',
            ],
            [
                'name' => 'Rádio AMC',
                'logo' => 'https://radioamc.com.br/public/97622-2023-12-30.png',
                'website' => 'https://radioamc.com.br/',
                'endpoint' => 'https://stm16.painelcast.com:6728/stats?json=1',
                'listeners_path' => 'currentlisteners',
            ],
            [
                'name' => 'Rádio UP!',
                'logo' => 'https://radioup.antbr.com/og_logo.jpg',
                'website' => 'https://radioup.antbr.com/',
                'endpoint' => 'https://saber.intra.antbr.com:8443/status-json.xsl',
                'listeners_path' => 'icestats.source.listeners',
            ],
        ];

        collect($stations)->each(fn (array $station) => RadioStation::updateOrCreate(
            ['name' => $station['name']],
            [...$station, 'is_active' => true]
        ));
    }
}
