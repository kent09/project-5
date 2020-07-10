<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    // use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    protected function randStrGen($len)
    {
        $result 		= "";
        $chars 			= "abcdefghijklmnopqrstuvwxyz";
        $charArray 		= str_split($chars);
        
        for ($i = 0; $i < $len; $i++) {
            $randItem 	= array_rand($charArray);
            $result    .= "".$charArray[$randItem];
        }
        return $result;
    }
    
    protected function sendEmail($to = array(), $subject, $content, $cc = array(), $bcc = array())
    {
        \Mail::send('emails.sendMail', ['content' => $content], function ($message) use ($to,$subject,$cc,$bcc) {
            $message->from('help@fusedtools.com', 'FusedTools');
            $message->to($to)->subject($subject);
            $message->cc($cc);
            $message->bcc($bcc);
        });
        return true;
    }
}
