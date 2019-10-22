<?php

namespace App\Utils;

class NotificationService{

    // ###########################################################################
    // ########################## CONFIG FIREBASE FCM ############################
    const URL_API_FCM = "https://fcm.googleapis.com/fcm/send";
    const TOKEN_ACCES_SERVER_FIREBASE = 'AAAAIv70EkE:APA91bGetKCqMPfkVuRLn7nT9qqMgUdzc9mN5lB-ny9_XX1gQjdITjfcFE2NxPC_3I3c43XzwcVb8Y6RvT5I55hhScGpT8zDfkWKdFcXdlTCDhTHJo42ahHF-PI4bhrOcfxUqGrRz-lT';

    // ###########################################################################
    // ###########################################################################

    // envia una notificacion con titulo y el mensaje recibido al dispositivo correspondiente
    public function sendSimpleNotificationFCM($titleNotif, $tokenDev, $msg)
    {
        // estructura de la notificacion que se requiere
        $notif = (object) null;
        $notif->title = $titleNotif;
        $notif->body = $msg;

        $msgtest = (object) null;
        $msgtest->token = $tokenDev;
        $msgtest->notification = $notif;

        // dato a mandar, probando mandar mas datos
        $response = (object) null;
        $response->message = $msgtest;

        // especificamos a quien mandar la notificacion y los datos a mandar
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