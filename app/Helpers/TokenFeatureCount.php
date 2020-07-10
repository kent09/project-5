<?php

namespace App\Helpers;

use App\UserSubscription;
use App\ToolFeature;
use App\UsageLog;



class TokenFeatureCount {

    const TOOL_FEATURES_DOC = 'Document Sending';
    const TOOL_FEATURES_SCRIPT = 'Script Tasks';
    const TOOL_FEATURES_CSV = 'CSV Import';
    const TOOL_FEATURES_GEO = 'Geo Tools';
    const TOOL_FEATURES_XERO = 'Xero Tools';


    public static function tokenValue($tool_features_description, $data) {
        
        $tool_feature = ToolFeature::where('description', $tool_features_description)->first();
    
        $result = 0;
        switch($tool_features_description) {
            case self::TOOL_FEATURES_SCRIPT: 
                $result = count($data) / $tool_feature->amount_per;
                break;
            case self::TOOL_FEATURES_CSV:
                $result = count($data) / $tool_feature->amount_per;
                break;
            case self::TOOL_FEATURES_GEO:
                $result = count($data) / $tool_feature->amount_per;
                break;
            case self::TOOL_FEATURES_DOC:
                $result = count($data) * $tool_feature->token_cost;
                break;
            case self::TOOL_FEATURES_XERO:
                $result = count($data) * $tool_feature->token_cost;
                break;
        }
        return $result;
    }

    public static function useToken($user_id, $token) {
        $subscrib_user = UserSubscription::where('user_id', $user_id)->latest()->decrement('token_count', $token);
    }

    public static function usage_log($user_id, $token, $tool_features_description) {
        $subscrib_user = UserSubscription::where('user_id', $user_id)->latest()->first();
        $tool_feature = ToolFeature::where('description', $tool_features_description)->first();
        $usage = new UsageLog;
        

    }

}