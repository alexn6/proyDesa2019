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

use App\Utils\NotificationService;
use App\Utils\Constant;

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
          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))){
            $idUser = $dataRequest->idUsuario;
            $idCompetition = $dataRequest->idCompetencia;
        
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

                if(($solicitante == NULL)&&($participante == NULL)){
                  // creamos un usuario_competencia aun sin rol
                  $newUser = new UsuarioCompetencia();
                  $newUser->setUsuario($user);
                  $newUser->setCompetencia($competition);
                  // atacamos el caso en que el usuario es organizador de la competencia
                  // pasaria derecho a ser un participante de la misma
                  if($organizador != NULL){
                    $newUser->setRol($rolCompetidor);
                    $newUser->setAlias("el_org");

                    $respJson->messaging = "Por ser organizador de la competencia su solicitud es aprobada. Ya es un competidor";
                  }
                  else{
                    $newUser->setRol($rolSolicitante);
                    $newUser->setAlias("solicit");

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
            $user = $repositoryUser->find($idUser);
            $competition = $repositoryComp->find($idCompetition);
        
            // vamos a buscar el elemento
            $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);

            $solicitante = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => "SOLICITANTE"]);
            $seguidorSolic = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition, 'rol' => "SEG-SOLIC"]);

            // persistimos el nuevo dato
            $em = $this->getDoctrine()->getManager();
            
            // uno de los 2 se borra seguro dado q esta solicitud vendria de la lista 
            // de solicitantes de una competencia
            // borramos el solicitante
            if($solicitante != NULL){
                $em->remove($solicitante);
            }
            // o borramos el seguidor-solicitante
            if($seguidorSolic != NULL){
                $em->remove($seguidorSolic);
            }
            $em->flush();
            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "Borrado con exito";
            $msg = "Su solicitud de inscripcion fue rechazada";
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
    

    // /**
    //  * Actualiza el rol de usuario_competencia
    //  * @Rest\Put("/usercomp-rol"), defaults={"_format"="json"})
    //  * 
    //  * @return Response
    //  */
    // public function updateRol(Request $request){

    //     $respJson = (object) null;
    //     $statusCode;

    //     if(!empty($request->getContent())){

    //       // recuperamos los datos del body y pasamos a un array
    //       $dataRequest = json_decode($request->getContent());

    //       if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))&&(!empty($dataRequest->rol))){
    //         // vemos si existen los datos necesarios
    //         $idUser = $dataRequest->idUsuario;
    //         $idCompetition = $dataRequest->idCompetencia;
    //         $nuevo_rol = $dataRequest->rol;

    //         $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
    //         // controlamos que el nombre de usuario este disponible
    //         $usuario_comp = $repository->findOneBy(['usuario' => $idUser, 'competencia' => $idCompetition]);
            
    //         $em = $this->getDoctrine()->getManager();
    //         $usuario_comp->setRol($nuevo_rol);
    //         $em->flush();
    
    //         $statusCode = Response::HTTP_OK;
    //         $respJson->messaging = "Actualizacion realizada";
    //       }
    //       else{
    //         $statusCode = Response::HTTP_BAD_REQUEST;
    //         $respJson->messaging = "Solicitud mal formada";
    //       }
          
    //     }
    //     else{
    //       $statusCode = Response::HTTP_BAD_REQUEST;
    //       $respJson->messaging = "Solicitud mal formada";
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

        $respJson = json_encode($respJson);

        // var_dump($respJson);

        $response = new Response($respJson);
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
          $competenciaNoSeguida = $competencia->getNombre();
          // buscamos el seguidor
          $follower = $repository->findOneBy(['usuario' => $usuario, 'competencia' => $competencia, 'rol' => $rolSeguidor]);
          // eliminamos el dato y refrescamos la DB
          $em = $this->getDoctrine()->getManager();
          $em->remove($follower);
          $em->flush();

          $statusCode = Response::HTTP_OK;
          $respJson->messaging = "Ya no es seguidor de la competencia: ".$competenciaNoSeguida;
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

        // buscamos el rol correspondiente
        $rol = $repositoryRol->findOneBy(['nombre' => $nameRol]);

        //var_dump($usuario_comp);
        // vemos si hay que actualizar o crear un nuevo dato
        if($usuario_comp != NULL){
          // actualizamos el dato y lo persistimos
          $em = $this->getDoctrine()->getManager();
          $usuario_comp->setRol($rol);
          $em->flush();
        }
        else{
          $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
          $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
          $user = $repositoryUser->find($idUser);
          $competition = $repositoryComp->find($idCompetition);

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
        $title = "Resolucion de inscripcion";

        $servNotification = new NotificationService();
        $servNotification->sendSimpleNotificationFCM($title, $tokenUser, $msg);
    }

    // notifica al usuario que su solicitud de incripcion a la competencia fue rechazada
    private function notifyInscriptionToOrganizators($arrayTokens, $nameCompetition, $nameUser){
        $title = "Inscripcion: ".$nameCompetition;
        $msg = "El usuario ".$nameUser." quiere formar parte de tu competencia";

        $servNotification = new NotificationService();
        $servNotification->sendMultipleNotificationFCM($title, $arrayTokens, $msg);
    }

}