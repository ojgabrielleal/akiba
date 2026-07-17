<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\External\AnimeThemeService;

use Illuminate\Http\Request;

class AnimeController extends Controller
{
    protected $animeThemeService;

    public function __construct(AnimeThemeService $animeThemeService)
    {
        $this->animeThemeService = $animeThemeService;
    }

    public function getMusic(Request $request)
    {
        $name = $request->query('name');
        $data = $this->animeThemeService->getMusics($name);

        return response()->json($data);
    }
}
