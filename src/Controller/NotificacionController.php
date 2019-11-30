<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Utils\NotificationService;

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
    // public function sendSimpleNotification(Request $request){
    //     $respJson = (object) null;
    //     $statusCode;

    //     // vemos si existe un body
    //     if(!empty($request->getContent())){
    //         // recuperamos los datos del body y pasamos a un objecto
    //         $requestBody = json_decode($request->getContent());
    //         $titleNotification = $requestBody->titulo;
    //         $bodyNotification = $requestBody->body;
    //         $tokenDevice = $requestBody->token;

    //         $servNotification = new NotificationService();
    //         $resultSend = $servNotification->sendSimpleNotificationFCM($titleNotification, $tokenDevice, $bodyNotification);

    //         $resultSend = json_decode($resultSend, true);

    //         $statusCode = Response::HTTP_OK;
    //         $respJson->msg = $resultSend;
    //     }
    //     else{
    //         $statusCode = Response::HTTP_BAD_REQUEST;
    //         $respJson->msg = "Peticion mal formada. No se realizao el envio de la notificacion";
    //     }

    //     $respJson = json_encode($respJson);

    //     $response = new Response($respJson);
    //     $response->headers->set('Content-Type', 'application/json');
    //     $response->setStatusCode($statusCode);

    //     return $response;
    // }

}