<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\InfusionSoftService;
use App\Http\Requests;

class PostCodeOwnerController extends Controller
{
    protected $infusionSoftService;
    
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->infusionSoftService = $infusionSoftService;
    }

    public function assignContactOwner(Request $request)
    {
        $params = $request->all();

        $fuse_key = (isset($params["FuseKey"]))?$params["FuseKey"]:"";
        $app_name = (isset($params["app_name"]))?$params["app_name"]:"";
        $country = (isset($params["country"]))?$params["country"]:"";
        $post_code = (isset($params["post_code"]))?$params["post_code"]:"";
        $conID = (isset($params["conID"]))?$params["conID"]:"";
        
        if ($fuse_key == "" || $app_name == "" || $country == "" || $post_code == "" || $conID == "") {
            echo "Important data missing!!!";
            exit;
        }

        $obj = new \App\Services\PostcodeTaggingService($this->infusionSoftService);
        $status = $obj->PostcBasedOwner($fuse_key, $app_name, $country, $post_code, $conID);
        echo $status;
    }
}
