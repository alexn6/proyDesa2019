<?php

namespace App\Utils;

use App\Utils\Constant;


// Admin de API verificador de mails
class VerificationMail
{

    public function verify($email)
    {
        $url = Constant::URL_API_EMAIL_VERIFICACTION."apiKey=".Constant::API_KEY_EMAIL_VERIFICATION."&emailAddress=".$email;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $dataResp = curl_exec($curl);
        
        $data = json_decode($dataResp, true);
        //var_dump($data);
        curl_close($curl);

        // si no existe el indice el xq la peticion dio error
        if(isset($data['smtpCheck'])){
            if(($data['smtpCheck'] == 'true')){
                return true;
            }
        }

        return false;
    }

}