<?php

namespace App\Utils;

use App\Utils\Constant;


// Admin de API verificador de mails
class VerificationMail
{

    public function verify($email)
    {
        $stringGet = Constant::URL_API_EMAIL_VERIFICACTION.'?apiKey='.Constant::API_KEY_EMAIL_VERIFICATION.'&emailAddress='.$email;

        $respJson = file_get_contents($stringGet);
        $respJson = json_decode($respJson, true);

        // var_dump($respJson);
        // echo $respJson['smtpCheck'];

        if($respJson['smtpCheck'] == 'true'){
            return true;
        }

        return false;
    }

}