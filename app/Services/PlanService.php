<?php
namespace App\Services;

use App\FusePlans;

class PlanService
{
    public function __construct()
    {
    }

    public function getFreePlan()
    {
        return FusePlans::where('name', 'Free')->get()->first();
    }

    public function getPlanById($id)
    {
        return FusePlans::where('id', $id)->first();
    }
}
