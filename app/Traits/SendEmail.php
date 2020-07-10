<?php
/**
 * Created by PhpStorm.
 * User: rohan
 * Date: 10/24/17
 * Time: 12:31 PM
 */

namespace App\Traits;

use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\PHPMailer;

trait SendEmail
{
    /**
     * @param $view the view that needs to be sent
     * @param $data the data that is needed by the view. If nothing then empty array
     * @param $email to whom it needs to be send
     * @param $name the name of the person to whom the email is sent
     * @param $subject the subject of the email
     */
    public static function sendEmails($view, $data, $email, $name, $subject)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSendmail();
//            $mail->SMTPDebug = 0;
            $mail->Host = env('MAIL_HOST');
            $mail->Port = env('MAIL_PORT');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->setFrom('help@fusedtools.com', 'FusedTools');
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $emailView =  view($view)->with($data);
            $mail->msgHTML($emailView);
            $mail->AltBody = 'This is a plain-text message body';

            return $mail->send();
        } catch (\Exception $e) {
        }
    }
}
