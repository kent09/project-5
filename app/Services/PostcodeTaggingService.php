<?php

namespace App\Services;

use App\PostcTags;
use App\PostcCountries;

use Illuminate\Http\Request;

use App\Services\InfusionSoftService;

use Illuminate\Support\Facades\DB;

define('F_KMPERMILE', 1.609344);

class PostcodeTaggingService
{
    public $infusionsoft_service;

    public function __construct($infusionsoft_service)
    {
        $this->infusionsoft_service = $infusionsoft_service;
    }

    public function getCoordsByPostc($country_code, $search_code)
    {
        $tablen = 'postc_codes_'.strtolower($country_code);
        $results = DB::select(DB::raw("SELECT * FROM `$tablen` WHERE `postcode`='$search_code' LIMIT 1"));
        if (count($results) > 0) {
            return $results[0];
        }
        return false;
    }

    public function getPostcByRadius($radius, $country_code, $search_code, $sLatitude, $sLongitude)
    {
        $tablen = 'postc_codes_'.strtolower($country_code);

        $fRadius = (double)$radius;
        $fLatitude = (double)$sLatitude;
        $fLongitude = (double)$sLongitude;
        $sXprDistance =  "SQRT(POWER(($fLatitude-latitude)*110.7,2)+POWER(($fLongitude-longitude)*75.6,2))";
        // 	static $aVals= array(1=> ", region1 AS statecode");
        $sXtraFields= ", region1 AS statecode";
    
        $results = DB::select(DB::raw("SELECT `suburb`, `longitude`, `latitude`, `postcode`, $sXprDistance AS distance $sXtraFields FROM `$tablen` WHERE $sXprDistance <= '$fRadius' ORDER BY distance ASC"));
        return $results;
    }
    
    public function getPostcList($pc_lat, $pc_long, $country_code, $search_code, $radius, $units)
    {
   
        //define('F_KMPERMILE', 1.609344);
        
        //convert miles to km for radius purposes
        $fradius=0;
        
        if ($units == "MI"): $fradius = $radius * F_KMPERMILE; elseif ($units == "KM"): $fradius = $radius;
        endif;
        $radius_pc = $this->getPostcByRadius($fradius, $country_code, $search_code, $pc_lat, $pc_long);
        
        $list = "";
        
        foreach ($radius_pc as $pc_value) {
            //get first character, add it to an array
            $list .= $pc_value->postcode."(".$pc_value->suburb."), ";
        }
         
        $result = $list;
             
        return $result;
    }
    

    public function tagBasedOnRadius($retag=0, $tagname="", $userId=0)
    {
        $tag_id = 0;
        $tagApplied = 0;
        if ($userId == 0) {
            $user = \Auth::user();
            if (!isset($user->id)) {
                return "User Authentication failed.";
            }
            $userId = $user->id;
        }

        //if it IS a retag, we need to query the data for that
        if ($retag >= 1) {
            $retag_data = PostcTags::where("id", $retag)->where("user_id", $userId)->first();

            //query it here

            if (isset($retag_data->id)) {

                //set the same variable names
                $country_code = $retag_data->postc_country_code;
                $account_id = $retag_data->infs_account_id;

                //query infusionsoft to ensure the tag we got from the DB still exists
                $tag_id = $this->infusionsoft_service->createTags($userId, $account_id, $tagname);
                
                if ($tag_id > 0) {
                    PostcTags::where("id", $retag)->where("user_id", $userId)->update(["tag_id"=>$tag_id]);

                    if ($retag_data->postc_type == 1 || $retag_data->postc_type == 0) {
                        //get postcode co-ordinants and id
                        $search_code = $retag_data->postc_code;
                        $radius = $retag_data->postc_radius;
                        $units = $retag_data->postc_units;
                        $postc_data = $this->getCoordsByPostc($country_code, $search_code);
    
                        if ($postc_data === false) {
                            return 'Postcode Not Found.';
                        }
                        //define('F_KMPERMILE', 1.609344);
                        //convert miles to km for radius purposes
                        $fradius=0;
                        if ($units == "MI"): $fradius = $radius * F_KMPERMILE; elseif ($units == "KM"): $fradius = $radius;
                        endif;
                        $post_list = $this->getPostcByRadius($fradius, $country_code, $search_code, $postc_data->latitude, $postc_data->longitude);
                    } elseif ($retag_data->postc_type == 2) {
                        $post_list = $retag_data->postc_list;
                        $post_list = $this->processPostCodeList($post_list);
                    }
                    
                    $short_pc = array();
                    $pc_list = array();
                
                    //shorthand the list to reduce infs queries by getting all the first characters, and make an array of just postcodes while doing it
                    foreach ($post_list as $pc_value) {
                        if ($retag_data->postc_type == 1 || $retag_data->postc_type == 0) {
                            //get first character, add it to an array
                            $char = substr($pc_value->postcode, 0, 1);
                            
                            //create de-duped list of postcodes
                            if (!in_array($pc_value->postcode, $pc_list)) {
                                $pc_list[] = $pc_value->postcode;
                            }
                        } elseif ($retag_data->postc_type == 2) {
                            //get first character, add it to an array
                            $char = substr($pc_value, 0, 1);
                            
                            $pc_list[] = $pc_value;
                        }

                        if (!in_array($char, $short_pc)) {
                            $short_pc[] = $char;
                        }
                    }#end of $radius_pc loop
                    
                    // echo "<pre>"; print_r($radius_pc); print_r($short_pc); print_r($pc_list);
                    $cons = array();
                    //query contacts from infusionsoft
                    $i = 0;
                    $tag_contacts = array();
                    foreach ($short_pc as $short_val) {

                        //need to add pagination here
                        $cons_result = $this->infusionsoft_service->searchContact($userId, $account_id, array('PostalCode' => $short_val.'%'));
                        // print_r($cons_result); exit;
                        //loop through returned contacts
                        
                        if (count($cons_result) >= 1) {
                            foreach ($cons_result as $con_value) {
    
                                //see if their postcode is in our list of radius tags
                                if (isset($con_value['PostalCode']) && in_array($con_value['PostalCode'], $pc_list)) {
                                
                                    //if they are, tag them
                                    if (!in_array($con_value['Id'], $tag_contacts)) {
                                        $tag_contacts[] = $con_value['Id'];
                                        // if(count($tag_contacts) == 100) {
                                        //     $add = $this->infusionsoft_service->handler($this->infusionsoft_service->bulkAssignTag($userId, $account_id, $tag_contacts, $tag_id));
                                        //     $tag_contacts = array();
                                        // }
                                    }
                                    $i++;
                                }
                            }
                            //needed to tag residual contacts
                        }
                    }#end of $short_pc loop
                    $add = $this->infusionsoft_service->handler([$this->infusionsoft_service, 'bulkAssignTag'], [$userId, $account_id, $tag_contacts, $tag_id,"App\PostcTags","tag_count",$retag_data->id]);
                    $tagApplied = $i;
                }#end of tag_id > 0
            }#end of is_array if
        }#end of retag>0
        
        return $tagApplied;
    }#end of function

    

    public function deleteAreaRmvTag($post_id, $reapply, $user_id=0)
    {
        if (!isset($post_id)) {
            return 'Area ID is missing.';
        }
        if ($user_id == 0) {
            $user = \Auth::user();
            if (!isset($user->id)) {
                return "User Authentication failed.";
            }
            $user_id = $user->id;
        }
        $area_data = PostcTags::where("id", $post_id)->where("user_id", $user_id)->first();
        if (isset($area_data->id) && $area_data->tag_id != 0) {
            \Log::info('deleteAreaRmvTag() Executed... ');
            \Log::info("with post_id - {$post_id} and user_id - {$user_id}");

            #create infusionsoft object here
            $this->infusionsoft_service->checkIfRefreshTokenHasExpired($user_id, $area_data->infs_account_id);
            // $test_data = $this->infusionsoft_service->fetAllContacts();

            if ($reapply == 1) {
                \Log::info('Re-apply Executed... ');

                $contacts = $this->infusionsoft_service->fetchContactWithGroupId($user_id, $area_data->infs_account_id, $area_data->tag_id);
               
                if (count($contacts) > 0) {
                    \Log::info("Contacts Found: " . count($contacts));
               
                    $tag_contacts = array();
                    foreach ($contacts as $con) {
                        $tag_contacts[] = $con['ContactId'];
                        // if(count($tag_contacts) == 100) {
                       //     $remove = $this->infusionsoft_service->handler($this->infusionsoft_service->bulkRmvTag($user_id, $area_data->infs_account_id, $tag_contacts, $area_data->tag_id));
                       //      $tag_contacts = array();
                       //  }
                    }
                   
                    //needed to tag residual
                    $remove = $this->infusionsoft_service->handler([$this->infusionsoft_service, 'bulkRmvTag'], [$user_id, $area_data->infs_account_id, $tag_contacts, $area_data->tag_id,"App\PostcTags","tag_count",$post_id]);

                    \Log::info("Removed Tag Residual: " . print_r($remove, true));
                }
            } else {
                #delete the tag from the infusionsoft
                $success = $this->infusionsoft_service->handler([$this->infusionsoft_service, 'deleteTag'], [$user_id, $area_data->infs_account_id, $area_data->tag_id]);

                \Log::info("Tag Deleted from the Infusionsoft: " . print_r($success, true));
            }

            //query it here

            if (isset($success) && $reapply != 1) {
                //set status to 0 for deleted
                PostcTags::where("id", $post_id)->update(["status"=>0]);
            }
        } else {
            return "No data found with given post_id=$post_id.";
        }
    }#end of function

    public function PostcBasedOwner($fuse_key, $app_name, $country, $post_code, $conID)
    {

        #find the user id using fuse_key
        $user_res = \App\User::where("FuseKey", $fuse_key)->first();
        if (!isset($user_res->id)) {
            return "User not found.";
        }

        $user_id = $user_res->id;

        #find the infusionsoft account ID using app_name
        $infs_res = \App\InfsAccount::where("user_id", $user_id)->where("name", $app_name)->first();
        if (!isset($infs_res->id)) {
            return "Invalid infusionsoft account.";
        }

        $infs_account_id = $infs_res->id;

        //get the country code first
        $country_code = PostcCountries::where('country_code', '<>', '')->where('country_name', $country)->first();

        if (isset($country_code->country_code)) {
            $country_code = $country_code->country_code;
        } else {
            return 'Could not find country.';
        }

        //check for postcode list results first as it requires less db queries
        $result_list = DB::table('postc_owner')
            ->join('postc_owner_groups', 'postc_owner.id', '=', 'postc_owner_groups.postc_owner_id')
            ->where("postc_owner.user_id", $user_id)
            ->where("postc_owner.infs_account_id", $infs_account_id)
            ->where("postc_owner.status", 1)

            ->where("postc_owner_groups.postc_country_code", $country_code)
            ->where("postc_owner_groups.status", 1)
            ->where("postc_owner_groups.match_type", 2)

            ->select('postc_owner_groups.*', 'postc_owner.infs_person_id')
            ->get();

        if (!isset($result_list) && count($result_list) > 0) {
            foreach ($result_list as $res_value) {
                $ps_list = explode(',', $res_value->postc_list);

                //first lets see if its just in the array as a solo postcode
                if (in_array($post_code, $ps_list)) {
                    $owner = $res_value->infs_person_id;
                    break;
                }

                //account for * and  -
                foreach ($ps_list as $post) {
                    if (strpos($post, '-') !== false) {
                        $limits = explode('-', $post);

                        $lower = $limits[0];
                        $upper = $limits[1];

                        if ($post_code >= $lower && $post_code <= $upper) {
                            $owner = $res_value->infs_person_id;
                            break 2;
                        }
                    } elseif (strpos($post, '*') !== false) {
                        $lower = str_replace('*', '0', $post);
                        $upper = str_replace('*', '9', $post);

                        if ($post_code >= $lower && $post_code <= $upper) {
                            $owner = $res_value->infs_person_id;
                            break 2;
                        }
                    }
                }
            }
        }


        //if we haven't found the owner, then lets try the radius results
        if (!isset($owner)) {

            //check for postcode radius results //NOTE need to add in the user_id and infusionsoft_id

            $group_results = DB::table('postc_owner')
                ->join('postc_owner_groups', 'postc_owner.id', '=', 'postc_owner_groups.postc_owner_id')
                ->where("postc_owner.user_id", $user_id)
                ->where("postc_owner.infs_account_id", $infs_account_id)
                ->where("postc_owner.status", 1)
    
                ->where("postc_owner_groups.postc_country_code", $country_code)
                ->where("postc_owner_groups.status", 1)
                ->where("postc_owner_groups.match_type", 1)
    
                ->select('postc_owner_groups.*', 'postc_owner.infs_person_id')
                ->get();

            //loop through the groups
            foreach ($group_results as $gres) {
                $units = strtoupper($gres->postc_units);
                $radius = $gres->postc_radius;
                $search_code = $gres->postc_code;

                $pcodes = $this->getPostCodeListUsingRadius($country_code, $units, $radius, $search_code);

                if (in_array($post_code, $pcodes)) {
                    $owner = $gres->infs_person_id;
                    break;
                }
            }
        }

        if (isset($owner)) {

           #create infusionsoft object here
            $this->infusionsoft_service->checkIfRefreshTokenHasExpired($user_id, $infs_account_id);

            $con_owner = $this->infusionsoft_service->updateDataInInfusionSoft("Contact", $conID, ['OwnerID' => $owner]);

            $opps = $this->infusionsoft_service->fetchDataFromINFSRecursive("Lead", 1000, ['ContactID' => $conID], ["Id"], "Id", true);

            if (is_array($opps) && count($opps) >= 1) {
                foreach ($opps as $opp) {
                    $opps = $this->infusionsoft_service->updateDataInInfusionSoft("Lead", $opp['Id'], array('UserID' => $owner));
                }#end of loop
            }
        } else {
            return 'No Owner Found.';
        }
    }

    public function ReAssignPostcBasedOwner($infs_account_id, $user_id)
    {
        $return = ["contacts_updated"=>0, "opp_updated"=>0];

        //get the postcode results //NOTE need to add in the user_id and infusionsoft_id
        $group_results = DB::table('postc_owner')
            ->join('postc_owner_groups', 'postc_owner.id', '=', 'postc_owner_groups.postc_owner_id')
            ->where("postc_owner.user_id", $user_id)
            ->where("postc_owner.infs_account_id", $infs_account_id)
            ->where("postc_owner.status", 1)
            ->where("postc_owner_groups.status", 1)

            ->select('postc_owner_groups.*', 'postc_owner.infs_person_id')
            ->get();

        $pcodes = array();

        if (is_array($group_results) && count($group_results) >= 1) {

            //loop through all the groups and make an associative array of postcodes for efficient querying later
            foreach ($group_results as $res_value) {
                $country_code = $res_value->postc_country_code;
                $person_id = $res_value->infs_person_id;

                //make sure the country and user are added as arrays
                if (!array_key_exists($country_code, $pcodes)) {
                    $pcodes[$country_code] = array();
                }
                if (!array_key_exists($person_id, $pcodes[$country_code])) {
                    $pcodes[$country_code][$person_id] = array();
                }

                //explode the postcodes and add them underneath the user
                if ($res_value->match_type == 2) {
                    $pcodes[$country_code][$person_id] = $this->processPostCodeList($res_value->postc_list);
                } elseif ($res_value->match_type == 1) {
                    $units = strtoupper($res_value->postc_units);
                    $radius = $res_value->postc_radius;
                    $search_code = $res_value->postc_code;

                    $pcodes[$country_code][$person_id] = $this->getPostCodeListUsingRadius($country_code, $units, $radius, $search_code);
                }
            }#end of $group_results loop

            //Now make an array of countries with country name as key
            //$sql= "SELECT `country_code`,`country_name` FROM `postc_countries`";
            $cresults = PostcCountries::where('country_code', '<>', '')->get();

            $carray = array();

            foreach ($cresults as $cvals) {
                $carray[$cvals->country_name] = $cvals->country_code;
            }

            //Now we have the associative array, we need to query the infs account.
            $infs_results = $this->infusionsoft_service->searchContact($user_id, $infs_account_id, ["PostalCode"=>"~<>~","Country" => "~<>~"]);

            foreach ($infs_results as $contact) {
                $con_country = $contact['Country'];

                //skip them if they dont have a postcode or their country isnt in the master array
                if (!isset($carray[$con_country]) || !isset($pcodes[$carray[$con_country]]) || !isset($contact['PostalCode'])) {
                    continue;
                }

                foreach ($pcodes[$carray[$con_country]] as $pers_key => $pers_codes) {
                    if (in_array($contact['PostalCode'], $pers_codes)) {
    
                        //set owner
                        $owner =  $pers_key;

                        $contactID = $contact['Id'];

                        $con_owner = $this->infusionsoft_service->updateDataInInfusionSoft("Contact", $contactID, ['OwnerID' => $owner]);
                        if (is_numeric($con_owner) && $con_owner > 0) {
                            $return["contacts_updated"]++;
                        }

                        $opps = $this->infusionsoft_service->fetchDataFromINFSRecursive("Lead", 1000, ['ContactID' => $contactID], ["Id"], "Id", true);

                        if (is_array($opps) && count($opps) >= 1) {
                            foreach ($opps as $opp) {
                                $opps = $this->infusionsoft_service->updateDataInInfusionSoft("Lead", $opp['Id'], array('UserID' => $owner));
                                if (is_numeric($opps) && $opps > 0) {
                                    $return["opp_updated"]++;
                                }
                            }#end of loop
                        }

                        //we found the owner, so skip ahead
                        continue 2;
                    }
                }
            }
        }#end of result_groups if
        
        return $return;
    }#end of function
    
    public function getPostCodeListUsingRadius($country_code, $units, $radius, $search_code)
    {
        //get postcode co-ordinants and id
        $postc_data = $this->getCoordsByPostc($country_code, $search_code);

        if ($postc_data === false) {
            return []; //the postcode wasnt found
        }

        
    
        //convert miles to km for radius purposes
        if ($units == "MI"): $fradius = $radius * F_KMPERMILE; elseif ($units == "KM"): $fradius = $radius;
        endif;

        $pcodes = [];

        $radius_pc = $this->getPostcByRadius($fradius, $country_code, $search_code, $postc_data->latitude, $postc_data->longitude);

        //shorthand the list to reduce infs queries by getting all the first characters, and make an array of just postcodes while doing it
        foreach ($radius_pc as $pc_value) {
            if (!in_array($pc_value->postcode, $pcodes)) {
                $pcodes[] = $pc_value->postcode;
            }
        }

        return $pcodes;
    }#end of function
    
    #funtion to process postcode list
    public function processPostCodeList($postc_list)
    {
        $posts = explode(',', $postc_list);
        $pcodes = [];
        //account for * and  -
        foreach ($posts as $post) {
            if (strpos($post, '-') !== false) {
                $limits = explode('-', $post);
    
                $lower = $limits[0];
                $upper = $limits[1];
    
                while ($lower <= $upper) {
                    if (!in_array($lower, $pcodes)) {
                        $pcodes[] = $lower;
                    }

                    $lower++;
                }#end of while loop
            } elseif (strpos($post, '*') !== false) {
                $lower = str_replace('*', '0', $post);
                $upper = str_replace('*', '9', $post);
    
                while ($lower <= $upper) {
                    if (!in_array($lower, $pcodes)) {
                        $pcodes[] = $lower;
                    }
    
                    $lower++;
                }#end of while loop
            } else {
                if (!in_array($post, $pcodes)) {
                    $pcodes[] = $post;
                }
            }
        }#end of loop
        return $pcodes;
    }#end of function
}
