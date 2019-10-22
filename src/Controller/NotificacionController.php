<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

//use App\Entity\UsuarioCompetencia;

 /**
 * Notificacion controller
 * @Route("/api",name="api_")
 */
class NotificacionController extends AbstractFOSRestController
{
    /**
     * Envio de notificaciones
     * @Rest\Post("/notification"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function sendSimpleNotification(Request $request){
        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
            // recuperamos los datos del body y pasamos a un objecto
            $requestBody = json_decode($request->getContent());
            $titleNotification = $requestBody->titulo;
            $bodyNotification = $requestBody->body;
            $tokenDevice = $requestBody->token;

            $resultSend = $this->sendNotificationFCM($titleNotification, $bodyNotification, $tokenDevice);
            $resultSend = json_decode($resultSend, true);

            $statusCode = Response::HTTP_OK;
            $respJson->msg = $resultSend;
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->msg = "Peticion mal formada. No se realizao el envio de la notificacion";
        }

        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }


    // ############################################################################
    // ###################### FUNCIONES AUXILIARES ############################
    private function sendNotificationFCM($titleNotif, $bodyNotif, $tokenDev)
    {
        $URL  = "https://fcm.googleapis.com/fcm/send";  //API URL of FCM
    
        //$API_ACCESS_KEY_SERVER = 'AAAA9P-hCPk:APA91bHBrbHBDxwmtD6RdwK-MvyxV17WUmqsyHV5VTOgTL5hfTbUiiBV2x88Ywqj0Y51di-VaDB7CS2ihHns1Hjj5l-DAcrDseJaWommLVR47y2vMO9Iw4nRYgKTtc77J0ej8nF2QYjr'; // YOUR_FIREBASE_API_KEY
        $API_ACCESS_KEY_SERVER = 'AAAAIv70EkE:APA91bGetKCqMPfkVuRLn7nT9qqMgUdzc9mN5lB-ny9_XX1gQjdITjfcFE2NxPC_3I3c43XzwcVb8Y6RvT5I55hhScGpT8zDfkWKdFcXdlTCDhTHJo42ahHF-PI4bhrOcfxUqGrRz-lT';
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
}