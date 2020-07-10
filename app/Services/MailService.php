<?php
namespace App\Services;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class MailService
{
    // use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function sendEmail($to, $subject, $content, $cc = '', $bcc = '')
    {
        $rn = "\r\n";
        $boundary = md5(rand());
        $boundary_content = md5(rand());

        // Headers
        $headers  = 'From: FusedTools Support' . $rn;
        $headers .= 'Mime-Version: 1.0' . $rn;
        $headers .= "Content-type:text/html;charset=UTF-8" . $rn;
        $headers .= 'X-Mailer: PHP/' . phpversion();

        //adresses cc and ci
        if ($cc != '') {
            $headers .= 'Cc: ' . $cc . $rn;
        }
        if ($bcc != '') {
            $headers .= 'Bcc: ' . $cc . $rn;
        }
        $headers .= $rn;


        $msg = $content;

        mail($to, $subject, $msg, $headers);

        return true;
    }
}
