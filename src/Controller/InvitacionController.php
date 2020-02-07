<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\UsuarioCompetencia;
use App\Entity\Usuario;
use App\Entity\Competencia;
use App\Entity\Rol;
use App\Entity\Invitacion;

use Kreait\Firebase\Messaging\Notification;
use App\Utils\NotificationManager;
use App\Utils\Constant;

    /**
     * Invitacion controller
     * @Route("/api",name="api_")
     */
class InvitacionController extends AbstractFOSRestController
{
    /**
     * Devuelve todas los solicitantes de una competencia
     * @Rest\Get("/invitations"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getIntivationsByUser(Request $request){
        $repository = $this->getDoctrine()->getRepository(Invitacion::class);
        $userRepository = $this->getDoctrine()->getRepository(Usuario::class);
        $data = array();
        
        $respJson = (object) null;
        
        $user = $userRepository->find($request->get('idUsuario'));

        $idUsuario = $request->get('idUsuario');
        // vemos si recibimos algun parametro
        if(!empty($idUsuario)){
            $data = $repository->findBy(['usuarioDestino' => $user,'estado' => Constant::ESTADO_INV_NO_DEFINIDO]);
            
            $data= $this->get('serializer')->serialize($data, 'json', [
                'circular_reference_handler' => function ($object) {
                  return $object->getId();
                },
                'ignored_attributes' => ['usuarioscompetencias', '__initializer__', '__cloner__', '__isInitialized__','rol','alias','token','organizacion',
                'fase','minCompetidores','faseActual','frecDias','usuarioDestino']
              ]);
            // Convert JSON string to Array
            $array_comp = json_decode($data, true);
           // $data = null;
            $data = array();
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }
        //armo el array para el return 
        foreach ($array_comp as &$valor) {
            $aux = array();
            $aux['idInvitacion'] = $valor['id'];
            $aux['nombreOrg'] = $valor['usuarioCompOrg']['usuario']['nombreUsuario'];
            $aux['nombreComp'] = $valor['usuarioCompOrg']['competencia']['nombre'];
            $aux['categoria'] = $valor['usuarioCompOrg']['competencia']['categoria']['nombre'];
            array_push($data,$aux);
        }

        $array_comp = json_encode($data);

        $response = new Response($array_comp);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
   
    /**
     * Cambia el estado de una invitacion
     * @Rest\Put("/invitations/upstatus"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function acceptInvitationCoorg(Request $request){
        $respJson = (object) null;
        
        // vemos si existe un body
        if(!empty($request->get('idInvitacion'))){
            // recuperamos los parametros recibidos
            $idInvitacion = $request->get('idInvitacion');
            
            $repository = $this->getDoctrine()->getRepository(Invitacion::class);
            // buscamos el usuario por su correo y nombre de usuario
            $invitacion = $repository->find($idInvitacion);
    
            // vemos si existe la invitacion para actualizar su estado
            if($invitacion == NULL){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Invitacion inexistente";
            }
            else{  
                // cambiamos el esatdo
                $invitacion->setEstado(Constant::ESTADO_INV_ALTA);

                $user = $invitacion->getUsuarioDestino();
                $competition = $invitacion->getUsuarioCompOrg()->getCompetencia();

                $repositoryRol = $this->getDoctrine()->getRepository(Rol::class);
                $rol = $repositoryRol->findOneBy(['nombre' => Constant::ROL_COORGANIZADOR]);

                // Se envia notificacion con el resultado al organizador
                $idUserCompOrg = $invitacion->getUsuarioCompOrg()->getUsuario()->getId();
                $mjeresolucion = "ACEPTO";
               //$this->sendResultInvitacion($user->getNombreUsuario(), $idUserCompOrg, $mjeresolucion);
                
                // creamos la nueva fila en usuario competencia
                $newUserComp = new UsuarioCompetencia();
                $newUserComp->setUsuario($user);
                $newUserComp->setCompetencia($competition);
                $newUserComp->setRol($rol);
        
                $em = $this->getDoctrine()->getManager();
                $em->persist($invitacion);
                $em->persist($newUserComp);
                $em->flush();
        
                $statusCode = Response::HTTP_OK;
                $respJson->messaging = "Estado de la invitacion actualizado.";
            }
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
    
        $respJson = json_encode($respJson);
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
    
        return $response;
    }

    /**
     * Cambia el estado de una invitacion
     * @Rest\Put("/invitations/dwnstatus"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function rejectedInvitationCoorg(Request $request){
        $respJson = (object) null;
  
        // vemos si existe un body
        if(!empty($request->get('idInvitacion'))){
            // recuperamos los parametros recibidos
            $idInvitacion = $request->get('idInvitacion');
            
            $repository = $this->getDoctrine()->getRepository(Invitacion::class);
            // buscamos el usuario por su correo y nombre de usuario
            $invitacion = $repository->find($idInvitacion);
    
            // vemos si existe la invitacion para actualizar su estado
            if($invitacion == NULL){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Invitacion inexistente";
            }
            else{ 
                // cambiamos el esatdo
                $invitacion->setEstado(Constant::ESTADO_INV_BAJA);

                $userDestino = $invitacion->getUsuarioDestino()->getNombreUsuario();
                $idUserCompOrg = $invitacion->getUsuarioCompOrg()->getUsuario()->getId();
                $mjeresolucion = "RECHAZO";
                
                // Se enviar notificacion con el resultado al organizador
               // $this->sendResultInvitacion($userDestino, $idUserCompOrg, $mjeresolucion);

                $em = $this->getDoctrine()->getManager();
                $em->persist($invitacion);
                $em->flush();
        
                $statusCode = Response::HTTP_OK;
                $respJson->messaging = "Invitacion rechazada.";
            }
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
    
        $respJson = json_encode($respJson);
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
    
        return $response;
    }




    private function sendResultInvitacion($nameUserDest, $idUserOrg, $resolucion){
        $title = 'Resolucion de invitacion';
        $body = 'El usuario '.$nameUserDest.' '.$resolucion.' tu invitacion a formar parte de la organizacion';
        $notification = Notification::create($title, $body);

        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        $userorg = $repository->find($idUserOrg);

        $token = $userorg->getToken();

        NotificationManager::getInstance()->notificationSpecificDevices($token, $notification);
    }

}
