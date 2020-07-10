<?php

namespace App\Http\Controllers;

use URL;
use App\Services\InfusionsoftSyncServiceV2;
use App\Services\UserRoleService;
use App\Services\UserService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Services\PostcodeTaggingService;

use App\Services\InfusionSoftService;
use Infusionsoft\Infusionsoft;

class TestController extends Controller
{
    public $infusionsoft_service;

    public function __construct(InfusionSoftService $infusionsoft_service)
    {
        $this->infusionsoft_service = $infusionsoft_service;
    }
    public function index()
    {
        $data['test']['aw'] = 1;
//        $res = array_has($data, 'test.wa');
//        dd($res);
        $res = array_get($data, 'test.aw');
        dd($res);

        $userRoleService = new UserRoleService;
        $userService = new UserService($userRoleService);
        $infusionsoft = new Infusionsoft(
            [
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ]
        );
        $infusionsoftService = new InfusionsoftService($infusionsoft, $userService);

        $new = new InfusionsoftSyncServiceV2($infusionsoftService);

        $new->test();
        dd('here');

        $obj = new PostcodeTaggingService($this->infusionsoft_service);
        $country_code = "AU";
        $search_code = 3104;
        $lat_long = $obj->getCoordsByPostc($country_code, $search_code);
        // 		echo "<pre>"; print_r($lat_long); echo "</pre>";
        $radius_result = $obj->getPostcByRadius(25, $country_code, $search_code, $lat_long->latitude, $lat_long->longitude);
        echo "<pre>";
        print_r($lat_long);
        print_r($radius_result);
        echo "</pre>";
    }

    public function deleteAreaRmvTag(Request $request)
    {
        $obj = new PostcodeTaggingService($this->infusionsoft_service);
        $post_id = $request->get("post_id");

        $return = $obj->deleteAreaRmvTag($post_id);
        
        echo $return;
    }
}
