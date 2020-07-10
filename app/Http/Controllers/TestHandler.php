<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InfusionSoftService;

class TestHandler extends Controller
{
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->infusionSoftService = $infusionSoftService;
    }

    public function testCheckIfRefreshTokenHasExpired(Request $request)
    {
        return $this->infusionSoftService->getIfRefreshTokenHasExpired(36);
    }
}
