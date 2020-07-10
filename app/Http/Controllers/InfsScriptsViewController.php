<?php
namespace App\Http\Controllers;

use App\Services\InfusionSoftService;
use App\User;
use Auth;
use App\InfsAccount;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class InfsScriptsViewController extends Controller
{
    private $authUser 	 = '';
    protected $infusionSoftService;

    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->authUser = Auth::user();
        $this->infusionSoftService = $infusionSoftService;
    }
}
