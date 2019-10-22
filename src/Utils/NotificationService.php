<?php

namespace App\Utils;

//use App\Utils\GeneratorEncuentro;

class NotificationService{

    const URL_API_FCM = "https://fcm.googleapis.com/fcm/send";
    // const TOKEN_ACCES_SERVER_FIREBASE = 'AAAA9P-hCPk:APA91bHBrbHBDxwmtD6RdwK-MvyxV17WUmqsyHV5VTOgTL5hfTbUiiBV2x88Ywqj0Y51di-VaDB7CS2ihHns1Hjj5l-DAcrDseJaWommLVR47y2vMO9Iw4nRYgKTtc77J0ej8nF2QYjr';
    const TOKEN_ACCES_SERVER_FIREBASE = 'AAAAIv70EkE:APA91bGetKCqMPfkVuRLn7nT9qqMgUdzc9mN5lB-ny9_XX1gQjdITjfcFE2NxPC_3I3c43XzwcVb8Y6RvT5I55hhScGpT8zDfkWKdFcXdlTCDhTHJo42ahHF-PI4bhrOcfxUqGrRz-lT';

    public function sendNotificationFCM($titleNotif, $bodyNotif, $tokenDev)
    {
        $URL  = "https://fcm.googleapis.com/fcm/send";  //API URL of FCM
    
        //$API_ACCESS_KEY_SERVER = 'AAAA9P-hCPk:APA91bHBrbHBDxwmtD6RdwK-MvyxV17WUmqsyHV5VTOgTL5hfTbUiiBV2x88Ywqj0Y51di-VaDB7CS2ihHns1Hjj5l-DAcrDseJaWommLVR47y2vMO9Iw4nRYgKTtc77J0ej8nF2QYjr'; // YOUR_FIREBASE_API_KEY
        $API_ACCESS_KEY_SERVER = 'AAAAIv70EkE:APA91bGetKCqMPfkVuRLn7nT9qqMgUdzc9mN5lB-ny9_XX1gQjdITjfcFE2NxPC_3I3c43XzwcVb8Y6RvT5I55hhScGpT8zDfkWKdFcXdlTCDhTHJo42ahHF-PI4bhrOcfxUqGrRz-lT';
        //$TOKEN_DEVICE = 'fOYrDHi4IKQ:APA91bEb_W4aV9AsWQO5clEOJhjhLW362IMyvYAZtOXR4JU--zuwNTfAwrf9Pb3Zg_TWbUek7ctn2bulMOYjL_1c2B4T6f2Rk9fy2qM-x09kx9iR7KsbnVV_Teea-lMwtOZwERAbvloD';
        $TOKEN_DEVICE = $tokenDev;

        $notif = (object) null;
        // $notif->title = "Portugal vs. Denmark";
        // $notif->body = "El cuerpo de la notif";
        $notif->title = $titleNotif;
        $notif->body = $bodyNotif;

        $msg = (object) null;
        $msg->token = $TOKEN_DEVICE;
        $msg->notification = $notif;

        // dato a mandar
        $response = (object) null;
        $response->message = $msg;

        $fields = array('to' => $TOKEN_DEVICE, 'data' => $response);

        // print_r($response);
        // echo(json_encode($response));

        $headers = array('Authorization: key=' . $API_ACCESS_KEY_SERVER, 'Content-Type: application/json');

        #Send Reponse To FireBase Server    
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL, $URL);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));

        // $info = curl_getinfo($ch);
        // print_r($info);
        // print_r($info['request_header']);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // envia una notificacion con titulo y el mensaje recibido al dispositivo correspondiente
    public function sendSimpleNotificationFCM($titleNotif, $tokenDev, $msg)
    {
        $notif = (object) null;
        $notif->title = $titleNotif;
        $notif->body = $msg;

        $msgtest = (object) null;
        $msgtest->token = $tokenDev;
        $msgtest->notification = $notif;

        // $data = (object) null;
        // $data->test = "texto de tokenDev";

        // dato a mandar
        $response = (object) null;
        $response->message = $msgtest;
        // $response->data = $data;

        $fields = array('to' => $tokenDev, 'data' => $response);

        $headers = array('Authorization: key=' . $this::TOKEN_ACCES_SERVER_FIREBASE, 'Content-Type: application/json');

        // envia una respuesta al servidor firebase
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL, $this::URL_API_FCM);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // envia una notificacion con titulo y el mensaje recibido
    // a la lista de dispositivos recibidos
    public function sendMultipleNotificationFCM($titleNotif, $arrayTokens, $msg)
    {
        $notif = (object) null;
        $notif->title = $titleNotif;
        $notif->body = $msg;

        $msgfirebase = (object) null;
        $msgfirebase->notification = $notif;

        $arrayTokensDevices = array();

        // recuperamos solo los token de los datos recibidos
        for ($i=0; $i < count($arrayTokens) ; $i++) { 
            $token = $arrayTokens[$i]['token'];
            array_push($arrayTokensDevices, $token);
        }

        // dato a mandar
        $response = (object) null;
        $response->message = $msgfirebase;

        $fields = array('registration_ids' => $arrayTokensDevices, 'data' => $response);

        $headers = array('Authorization: key=' . $this::TOKEN_ACCES_SERVER_FIREBASE, 'Content-Type: application/json');

        // envia una respuesta al servidor firebase
        $ch = curl_init(); 
        curl_setopt($ch,CURLOPT_URL, $this::URL_API_FCM);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}