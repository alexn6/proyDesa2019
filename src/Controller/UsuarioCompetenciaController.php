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
use App\Entity\Invitacion;
use App\Entity\Rol;

use App\Utils\NotificationService;
use App\Utils\Constant;
use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;

 /**
 * UsuarioCompetencia controller
 * @Route("/api",name="api_")
 */
class UsuarioCompetenciaController extends AbstractFOSRestController
{
    /**
     * Devuelve todas los solicitantes de una competencia
     * @Rest\Get("/usercomp/petitioners"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getPetitionersByCompetition(Request $request){
        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
        $respJson = (object) null;

        $idCompetencia = $request->get('idCompetencia');
        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $respJson = $repository->findSolicitantesByCompetencia($idCompetencia);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson->petitioners = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Registrar la solicitud a inscripcion de un usuario
     * @Rest\Post("/usercomp"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function addSolicitante(Request $request){

        $respJson = (object) null;
        $statusCode;

        // controlamos que se haya recibido algo en el body
        if(!empty($request->getContent())){
          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          // vemos si existen los datos necesarios
          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))&&(!empty($dataRequest->alias))){
            $idUser = $dataRequest->idUsuario;
            $idCompetition = $dataRequest->idCompetencia;
            $alias = $dataRequest->alias;
            // controlamos que el nombre de usuario este disponible
            $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);

            // buscamos los datos correspodientes a los id recibidos
            $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
            $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
            $user = $repositoryUser->find($idUser);
            $competition = $repositoryComp->find($idCompetition);

            // controlamos que existan el uduario como la competencia
            if(($user != NULL) && ($competition != NULL)){
              // recuperamos los roles a usar
                $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
                $rolSolicitante = $repositoryRol->findOneBy(['nombre' => Constant::ROL_SOLICITANTE]);
                $rolCompetidor = $repositoryRol->findOneBy(['nombre' => Constant::ROL_COMPETIDOR]);
                $rolOrganizador = $repositoryRol->findOneBy(['nombre' => Constant::ROL_ORGANIZADOR]);
                // controlamos que no sea un solicitante repetido o competidor
                $solicitante = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => $rolSolicitante]);
                $participante = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => $rolCompetidor]);
                $organizador = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => $rolOrganizador]);
                // recuperamos los datos para la notificacion a los organizadores
                $nameCompetition = $competition->getNombre();
                $nameUser = $user->getNombre();
                // recuperamos los token de los organizadores
                $arrayToken = $repository->findOrganizatorsCompetencia($competition->getId());

                $aliasNoRep = $repository->comprobarAlias($idCompetition, $alias);

              if(empty($aliasNoRep)){
                      
                    if(($solicitante == NULL)&&($participante == NULL)){
                      // creamos un usuario_competencia aun sin rol
                      $newUser = new UsuarioCompetencia();
                      $newUser->setUsuario($user);
                      $newUser->setCompetencia($competition);
                      // atacamos el caso en que el usuario es organizador de la competencia
                      // pasaria derecho a ser un participante de la misma
                      if($organizador != NULL){
                        $newUser->setRol($rolCompetidor);
                        $newUser->setAlias($alias);

                        $respJson->messaging = "Por ser organizador de la competencia su solicitud es aprobada. Ya es un competidor";
                      }
                      else{
                        $newUser->setRol($rolSolicitante);
                        $newUser->setAlias($alias);

                        $respJson->messaging = "Solicitud registrada";
                                    
                        // notificamos a los organizadores de la solicitud de inscripcion
                        $this->notifyInscriptionToOrganizators($arrayToken, $nameCompetition, $nameUser);
                      }
                      // persistimos el nuevo dato
                      $em = $this->getDoctrine()->getManager();
                      $em->persist($newUser);
                      $em->flush();

                      $statusCode = Response::HTTP_OK;
                    }
                    else{
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        if($solicitante != NULL){
                            $respJson->messaging = "El usuario ya es SOLICITANTE de la competencia";
                        }
                        else{
                            $respJson->messaging = "El usuario ya es PARTICIPANTE de la competencia";
                        }
                    }
              }else{
                $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "Alias registrado";
              }
            }
            else{
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "El usuario y/o competencia no existen";
            }
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
          } 
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Solicitud mal formada";
        }
        
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Aprueba la solicitud a inscripcion de un usuasrio en una competencia.
     * Pre: los usuarios y competencia recibidos existen,  el rol del usuario es SOLICITANTE
     * @Rest\Post("/add-participate"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function approvedSolicitud(Request $request){
        $respJson = (object) null;
        $statusCode;

        // controlamos que se haya recibido algo en el body
        if(!empty($request->getContent())){
          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          // vemos si existen los datos necesarios
          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            $idUser = $dataRequest->idUsuario;
            $idCompetition = $dataRequest->idCompetencia;

            // buscamos los datos correspodientes a los id recibidos
            $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
            $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
            $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
            $user = $repositoryUser->find($idUser);
            $competition = $repositoryComp->find($idCompetition);
            $rolCompetidor = $repositoryRol->findOneBy(['nombre' => Constant::ROL_COMPETIDOR]);
            
            $rolSolicitante = $repositoryRol->findOneBy(['nombre' => Constant::ROL_SOLICITANTE]);

            // controlamos si el usuario tiene habilitadas las notificaciones
            if($user->getNotification()->getCompetidor()){
              // subscribimos al competidor a su topico correspondiente
              $this->subcribeUserToTopic($user->getToken(), $competition, $rolCompetidor);
            }
            
            // vamos a buscar el elemento
            $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);
            $solicitante = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => $rolSolicitante]);

            // persistimos el nuevo dato
            $em = $this->getDoctrine()->getManager();
            $solicitante->setRol($rolCompetidor);
            $em->flush();

            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "Actualizado rol del ususario a PARTICIPANTE";
            $msg = "¡¡Solicitud de inscripcion aprobada!!";
            // enviamos la notificacion al usuario
            $this->notifySolInscription($user->getToken(), $competition->getNombre(), $msg);
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
            }
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
        }

        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    

    /**
     * Elimina una solicitud de inscripcion
     * Pre: los usuarios y competencia recibidos existen
     * @Rest\Post("/usercomp-del"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function deleteSolicitante(Request $request){

        $respJson = (object) null;
        $statusCode;

        // controlamos que se haya recibido algo en el body
        if(!empty($request->getContent())){
          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          // vemos si existen los datos necesarios
          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            $idUser = $dataRequest->idUsuario;
            $idCompetition = $dataRequest->idCompetencia;

            // buscamos los datos correspodientes a los id recibidos
            $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
            $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
            $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
            $rolSolicitante = $repositoryRol->findOneBy(['nombre' => Constant::ROL_SOLICITANTE]);

            $user = $repositoryUser->find($idUser);
            $competition = $repositoryComp->find($idCompetition);
        
            // vamos a buscar el elemento
            $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);

            $solicitante = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => $rolSolicitante]);

            // persistimos el nuevo dato
            $em = $this->getDoctrine()->getManager();
            
            // uno de los 2 se borra seguro dado q esta solicitud vendria de la lista 
            // de solicitantes de una competencia
            // borramos el solicitante
            if($solicitante != NULL){
                $em->remove($solicitante);
                // o borramos el seguidor-solicitante
                $em->flush();
                $statusCode = Response::HTTP_OK;
                $respJson->messaging = "Borrado con exito";
                $msg = "Su solicitud de inscripcion fue rechazada";
                // enviamos la notificacion al usuario
                $this->notifySolInscription($user->getToken(), $competition->getNombre(), $msg);
              } else{
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "No existe usuario";
              }
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
          }
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
        }

        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

  //   /**
  //    * Se encarga de procesar la resolucion de la invitacion a Co-organizador
  //    * Pre: los usuarios y competencia recibidos existen
  //    * @Rest\Post("/resol-invitation-coorg"), defaults={"_format"="json"})
  //    * 
  //    * @return Response
  //    */
  //   public function resolInvitationCoorganizator(Request $request){
  //     $respJson = (object) null;
  //     $statusCode;

  //     // controlamos que se haya recibido algo en el body
  //     if(!empty($request->getContent())){
  //       // recuperamos los datos del body y pasamos a un array
  //       $dataRequest = json_decode($request->getContent());

  //       // vemos si existen los datos necesarios
  //       if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))&&(!empty($dataRequest->resolucion))){
  //         $idUser = $dataRequest->idUsuario;
  //         $idCompetition = $dataRequest->idCompetencia;
  //         $resolucion = $dataRequest->resolucion;

  //         // buscamos los datos correspodientes a los id recibidos
  //         $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
  //         $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
  //         $user = $repositoryUser->find($idUser);
  //         $competition = $repositoryComp->find($idCompetition);

  //         $msgResolutionInvitation;

  //         // vemos si se acepta la solicitud
  //         if($resolucion === "aceptada"){
  //           $msgResolutionInvitation = "El usuario <".$user->getNombreUsuario()."> ha aceptado formar parte de la competencia";
  //           // creamos la nueva fila
  //           $newUser = new UsuarioCompetencia();
  //           $newUser->setUsuario($user);
  //           $newUser->setCompetencia($competition);
  //           $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
  //           $rolCoorg = $repositoryRol->findOneBy(['nombre' => Constant::ROL_COORGANIZADOR]);
  //           $newUser->setRol($rolCoorg);
  //           //var_dump($newUser);
  //           // persistimos el nuevo dato
  //           $em = $this->getDoctrine()->getManager();
  //           $em->persist($newUser);
  //           $em->flush();
  //         }
  //         else{
  //           $msgResolutionInvitation = "El usuario <".$user->getNombreUsuario()."> rechazo la invitacion a formar parte de la competencia";
  //         }

  //         $statusCode = Response::HTTP_OK;
  //         $respJson->messaging = "Invitacion a co-organizador resuelta.";
  //         // enviamos la notificacion al organizador
  //         // recuperamos el token del organizador
  //         $repositoryUserComp=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);
  //         $tokenOrganizator = $repositoryUserComp->findOrganizatorCompetencia($idCompetition);
  //         $this->notifyResolutionInvitationCoorg($tokenOrganizator[0]['token'], $competition->getNombre(), $msgResolutionInvitation);
  //       }
  //       else{
  //         $statusCode = Response::HTTP_BAD_REQUEST;
  //         $respJson->messaging = "Solicitud mal formada";
  //         }
  //     }
  //     else{
  //         $statusCode = Response::HTTP_BAD_REQUEST;
  //         $respJson->messaging = "Solicitud mal formada";
  //     }

  //     $respJson = json_encode($respJson);

  //     $response = new Response($respJson);
  //     $response->headers->set('Content-Type', 'application/json');
  //     $response->setStatusCode($statusCode);

  //     return $response;
  // }

    /**
     * Devuelve todas las competencias de un usuario
     * @Rest\Get("/competitionsuser"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCompetitionsByUser(Request $request){
        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
        $respJson = (object) null;

        $idUser = $request->get('idUsuario');
        // vemos si recibimos algun parametro
        if(!empty($idUser)){
            $respJson->competitions = $repository->findCompetitionsByUser($idUser);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson->competitions = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $respJson = json_encode($respJson);

        // var_dump($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Devuelve todas las competencias de un usuario
     * @Rest\Get("/competition-follow"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCompetitionFollowByUser(Request $request){
        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
        $respJson = (object) null;

        $idUser = $request->get('idUsuario');
        // vemos si recibimos algun parametro
        if(!empty($idUser)){
            $respJson = $repository->findCompetitionsFollowByUser($idUser);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson->competitions = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        // var_dump($respJson);

        // reemplazamos el rol (string) por un array de roles
        foreach ($respJson as &$valor) {
          $valor['rol'] = array($valor['rol']);
        }

        $respJson = json_encode($respJson);

        // var_dump($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Devuelve todas las competencias de un usuario
     * @Rest\Get("/competitors-competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCompetitorsByCompetition(Request $request){
        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
        $respJson = (object) null;

        $idCompetencia = $request->get('idCompetencia');
        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $respJson = $repository->findCompetidoresByCompetencia($idCompetencia);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $respJson = json_encode($respJson);

        // var_dump($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Devuelve todas las competencias en las que participa un usuario
     * @Rest\Get("/competition-participates"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCompetitionParticipatesByUser(Request $request){
        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
        $respJson = (object) null;

        $idUser = $request->get('idUsuario');
        // vemos si recibimos algun parametro
        if(!empty($idUser)){
            $respJson = $repository->findCompetitionsParticipatesByUser($idUser);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        // reemplazamos el rol (string) por un array de roles
        foreach ($respJson as &$valor) {
          $valor['rol'] = array($valor['rol']);
        }

        $respJson = json_encode($respJson);

        // var_dump($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
	 
    /**
     * Devuelve todas las competencias que organiza un usuario
     * @Rest\Get("/competition-organize"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getCompetitionOrganizeByUser(Request $request){
        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
        $respJson = (object) null;

        $idUser = $request->get('idUsuario');
        // vemos si recibimos algun parametro
        if(!empty($idUser)){
            $respJson = $repository->findCompetitionsOrganizeByUser($idUser);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        // reemplazamos el rol (string) por un array de roles
        foreach ($respJson as &$valor) {
          $valor['rol'] = array($valor['rol']);
        }

        $respJson = json_encode($respJson);

        // var_dump($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
 
    /**
     * Devuelve todas las solicitudes para coorganizar de un usuario
     * @Rest\Get("/competition-request"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function findCompetitionsRequestCoOrganizateByUser(Request $request){
        $respJson = (object) null;
        $statusCode;
        $idUser = null;

        $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
        $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);

        $idUser = $request->get('idUsuario');
        $user = $repositoryUser->find($idUser);

        // vemos si recibimos algun parametro
        if(!empty($idUser)){
            // $respJson = $repository->findCompetitionsRequestCoOrganizateByUser($idUser,$idCompetencia);
            // $statusCode = Response::HTTP_OK;
            $rolCoorg = $repositoryRol->findOneBy(['nombre' => Constant::ROL_SOLCOORG]);
            $respJson = $repository->findBy(['usuario' => $user, 'rol' => $rolCoorg]);

            $respJson = $this->get('serializer')->serialize($respJson, 'json', [
                'circular_reference_handler' => function ($object) {
                  return $object->getId();
                },
                'ignored_attributes' => ['usuarioscompetencias', '__initializer__', '__cloner__', '__isInitialized__','rol','usuario']
            ]);

            $array_comp = json_decode($respJson, true);
            $array_comp = json_encode($array_comp);

            if(empty($respJson)){
                $respJson->messaging = "No hay solicitudes.";   
                $statusCode = Response::HTTP_BAD_REQUEST; 
            }else{
                $statusCode = Response::HTTP_OK;
                //$respJson->messaging = "Hay solicitudes.";
            }
        }
        else{
            $respJson = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
        }
        $respJson = json_encode($array_comp);
        $response = new Response($array_comp);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    // ##################################################################
    // ###################### actualizacion de roles ####################
    /**
     * Actualiza o crea una nueva fila con el rol de usuario_competencia a SEGUIDOR
     * @Rest\Put("/usercomp-rolfollow"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function toRolFollow(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(!empty($request->getContent())){

          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            // vamos a actualizar o crear un nuevo dato con el rol del usuario
            $this->processUpdateRol($dataRequest, Constant::ROL_SEGUIDOR);

            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "Actualizacion realizada";
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
          }
          
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Solicitud mal formada";
        }
  
        
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Actualiza el rol de usuario_competencia a COMPETIDOR
     * @Rest\Put("/usercomp-rolcomp"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function toRolCompetitor(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(!empty($request->getContent())){

          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            // vamos a actualizar el rol del usuario
            $this->processUpdateRol($dataRequest, Constant::ROL_COMPETIDOR);

            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "Actualizacion realizada";
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
          }
          
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Solicitud mal formada";
        }
  
        
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Actualiza el rol de usuario_competencia a ORGANIZADOR
     * @Rest\Put("/usercomp-rolorg"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function toRolOrganizator(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(!empty($request->getContent())){

          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            // vamos a actualizar el rol del usuario
            $this->processUpdateRol($dataRequest, Constant::ROL_ORGANIZADOR);

            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "Actualizacion realizada";
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
          }
          
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Solicitud mal formada";
        }
  
        
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Actualiza el rol de usuario_competencia a CO-ORGANIZADOR
     * @Rest\Put("/usercomp-rolcoorg"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function toRolCoorganizator(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(!empty($request->getContent())){

          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            // vamos a actualizar el rol del usuario
            $this->processUpdateRol($dataRequest, Constant::ROL_COORGANIZADOR);

            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "Actualizacion realizada";
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Solicitud mal formada";
          }
          
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Solicitud mal formada";
        }
  
        
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Actualiza el rol de usuario_competencia a SEGUIDOR
     * @Rest\Put("/usercomp-delfollow"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function deleteFollower(Request $request){

      $respJson = (object) null;
      $statusCode;

      if(!empty($request->getContent())){

        // recuperamos los datos del body y pasamos a un array
        $dataRequest = json_decode($request->getContent());

        if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
          $idUsuario = $dataRequest->idUsuario;
          $idCompetencia = $dataRequest->idCompetencia;
          // recuperamos la fila y la eliminamos la fila
          $repository = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
          // recuperamos los roles a usar
          $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
          $rolSeguidor = $repositoryRol->findOneBy(['nombre' => Constant::ROL_SEGUIDOR]);
          // buscamos los datos correspodientes a los id recibidos
          $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
          $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
          $usuario = $repositoryUser->find($idUsuario);
          $competencia = $repositoryComp->find($idCompetencia);
          //$competenciaNoSeguida = $competencia->getNombre();

          $this->unsubcribeUserToTopic($usuario->getToken(), $competencia, $rolSeguidor);
          // buscamos el seguidor
          $follower = $repository->findOneBy(['usuario' => $usuario, 'competencia' => $competencia, 'rol' => $rolSeguidor]);
          // eliminamos el dato y refrescamos la DB
          $em = $this->getDoctrine()->getManager();
          $em->remove($follower);
          $em->flush();

          $statusCode = Response::HTTP_OK;
          $respJson->messaging = "Ya no es seguidor de la competencia: ".$competencia->getNombre();
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Solicitud mal formada";
        }
        
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Solicitud mal formada";
      }

      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);

      return $response;
  }

    // ##################################################################
    // ###################### funciones auxiliares ######################

    // se actualiza el rol de la fila de usuarioCompetencia
    private function processUpdateRol($dataRequest, $nameRol){
        // recuperamos los datos
        $idUser = $dataRequest->idUsuario;
        $idCompetition = $dataRequest->idCompetencia;

        $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        $repositoryRol=$this->getDoctrine()->getRepository(Rol::class);
    
        // buscamos la fila correspondiente
        $usuario_comp = $repository->findOneBy(['usuario' => $idUser, 'competencia' => $idCompetition]);
        
        $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
        $user = $repositoryUser->find($idUser);

        // buscamos el rol correspondiente
        $rol = $repositoryRol->findOneBy(['nombre' => $nameRol]);

        $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
        $competition = $repositoryComp->find($idCompetition);

        // susbcribimos en el caso de que sean seguidores o competidores
        // if(($nameRol == Constant::ROL_SEGUIDOR) || ($nameRol == Constant::ROL_COMPETIDOR)){
        //   // subscribimos al usuario al topico correspondiente
        //   $this->subcribeUserToTopic($user->getToken(), $competition, $rol);
        // }
        if($nameRol == Constant::ROL_SEGUIDOR){
          if($user->getNotification()->getSeguidor()){
            // subscribimos al usuario al topico correspondiente
            $this->subcribeUserToTopic($user->getToken(), $competition, $rol);
          }
        }
        if($nameRol == Constant::ROL_COMPETIDOR){
          if($user->getNotification()->getCompetidor()){
            // subscribimos al usuario al topico correspondiente
            $this->subcribeUserToTopic($user->getToken(), $competition, $rol);
          }
        }

        // vemos si hay que actualizar o crear un nuevo dato
        if($usuario_comp != NULL){
          // actualizamos el dato y lo persistimos
          $em = $this->getDoctrine()->getManager();
          $usuario_comp->setRol($rol);
          $em->flush();
        }
        else{

          $newUser = new UsuarioCompetencia();
          $newUser->setUsuario($user);
          $newUser->setCompetencia($competition);
          $newUser->setRol($rol);
          $newUser->setAlias("nueva fila");
          //var_dump($newUser);
          // persistimos el nuevo dato
          $em = $this->getDoctrine()->getManager();
          $em->persist($newUser);
          $em->flush();
        }
    }

    // notifica al usuario que su solicitud de incripcion a la competencia fue rechazada
    private function notifySolInscription($tokenUser, $nameCompetition, $msg){
      $title = "Resolución de inscripción";

      $notification = Notification::create($title, $msg);

      NotificationManager::getInstance()->notificationSpecificDevices($tokenUser, $notification);
  }

    // notifica al usuario que su solicitud de incripcion a la competencia fue rechazada
    // private function notifyInvitationCoorg($tokenUser, $nameCompetition, $msg){
    //   $title = "Invitacion a CO-ORGANIZADOR";

    //   $servNotification = new NotificationService();
    //   $servNotification->sendSimpleNotificationFCM($title, $tokenUser, $msg);
    // }

    // notifica al usuario que su solicitud de incripcion a la competencia fue rechazada
    // private function notifyResolutionInvitationCoorg($tokenUser, $nameCompetition, $msg){
    //   $title = "Resolucion de invitacion organizadores.";
    //   var_dump($tokenUser);
    //   // var_dump($nameCompetition);
    //   // var_dump($msg);
    //   $servNotification = new NotificationService();
    //   $servNotification->sendSimpleNotificationFCM($title, $tokenUser, $msg);
    // }

    // notifica al usuario que su solicitud de incripcion a la competencia fue rechazada
    // private function notifyInscriptionToOrganizators($arrayTokens, $nameCompetition, $nameUser){
    //     $title = "Inscripcion: ".$nameCompetition;
    //     $msg = "El usuario ".$nameUser." quiere formar parte de tu competencia";

    //     $servNotification = new NotificationService();
    //     $servNotification->sendMultipleNotificationFCM($title, $arrayTokens, $msg);
    // }
    private function notifyInscriptionToOrganizators($arrayTokens, $nameCompetition, $nameUser){
      $title = "Inscripcion: ".$nameCompetition;
      $body = "El usuario ".$nameUser." quiere formar parte de tu competencia";

      $tokenDevices = array();

      foreach ($arrayTokens as &$valor) {
        array_push($tokenDevices, $valor['token']);
      }

      $notification = Notification::create($title, $body);

      // var_dump($arrayTokens);
      // var_dump($tokenDevices);

      if(count($tokenDevices) > 0){
        NotificationManager::getInstance()->notificationMultipleDevices($tokenDevices, $notification);
      }
  }

    // subscribimos un usuario al topico correspondiente a su rol de una competencia
    private function subcribeUserToTopic($token, $competition, $rol){
      $nameCompFiltered = str_replace(' ', '', $competition->getNombre());
      $topic = $nameCompFiltered. '-' .$rol->getNombre();
      // var_dump("token: ".$token);
      // var_dump("topico: ".$topic);
      NotificationManager::getInstance()->subscribeTopic($topic, $token);
    }

    // subscribimos un usuario al topico correspondiente a su rol de una competencia
    private function unsubcribeUserToTopic($token, $competition, $rol){
      $nameCompFiltered = str_replace(' ', '', $competition->getNombre());
      $topic = $nameCompFiltered. '-' .$rol->getNombre();
      // var_dump("token: ".$token);
      // var_dump("topico: ".$topic);
      NotificationManager::getInstance()->unsubscribeTopic($topic, $token);
    }

}