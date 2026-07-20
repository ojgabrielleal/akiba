<?php

namespace App\Http\Controllers\Api\External;

use App\Http\Controllers\Controller;

use App\Services\External\AnimeThemeService;

use Illuminate\Http\Request;

class AnimeThemesController extends Controller
{
    public function __construct(
        private AnimeThemeService $animeThemeService,
    ) {}

    public function search(Request $request)
    {
        $query = $request->string('query')->toString();
        $data = $this->animeThemeService->search($query);

        return response()->json($data);
    }
}
