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

 /**
 * UsuarioCompetencia controller
 * @Route("/api",name="api_")
 */
class UsuarioCompetenciaController extends AbstractFOSRestController
{

    /**
     * Agrega un usuario SOLICITANTE
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
            //$usuario_comp = $repository->findOneBy(['usuario' => $idUser, 'competencia' => $idCompetition]);

            // buscamos los datos correspodientes a los id recibidos
            $repositoryUser=$this->getDoctrine()->getRepository(Usuario::class);
            $repositoryComp=$this->getDoctrine()->getRepository(Competencia::class);
            $user = $repositoryUser->find($idUser);
            $competition = $repositoryComp->find($idCompetition);

            // controlamos que existan el uduario como la competencia
            if(($user != NULL) && ($competition != NULL)){
                // controlamos que no sea un solicitante repetido
                $solicitante = $repository->findOneBy(['usuario' => $user, 'competencia' => $competition]);
                if($solicitante == NULL){
                    // creamos el nuevo solicitante
                    $newUserSolicitante = new UsuarioCompetencia();
                    $newUserSolicitante->setUsuario($user);
                    $newUserSolicitante->setCompetencia($competition);
                    $newUserSolicitante->setRol("SOLICITANTE");
                    $newUserSolicitante->setAlias("solicit");
                    
                    // persistimos el nuevo dato
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newUserSolicitante);
                    $em->flush();
            
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "Solicitud registrada.";
                }
                else{
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "El usuario ya es SOLICITANTE de la competencia";    
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
     * Actualiza el rol de usuario_competencia
     * @Rest\Put("/usercomp-rol"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function updateRol(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(!empty($request->getContent())){

          // recuperamos los datos del body y pasamos a un array
          $dataRequest = json_decode($request->getContent());

          if((!empty($dataRequest->idUsuario))&&(!empty($dataRequest->idCompetencia))&&(!empty($dataRequest->rol))){
            // vemos si existen los datos necesarios
            $idUser = $dataRequest->idUsuario;
            $idCompetition = $dataRequest->idCompetencia;
            $nuevo_rol = $dataRequest->rol;

            $repository=$this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        
            // controlamos que el nombre de usuario este disponible
            $usuario_comp = $repository->findOneBy(['usuario' => $idUser, 'competencia' => $idCompetition]);
            
            $em = $this->getDoctrine()->getManager();
            $usuario_comp->setRol($nuevo_rol);
            $em->flush();
    
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
            $respJson->competitions = $repository->findCompetitionsFollowByUser($idUser);
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
            $respJson->competitors = $repository->findParticipanteByCompetencia($idCompetencia);
            $statusCode = Response::HTTP_OK;
        }
        else{
            $respJson->competitors = NULL;
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
            $respJson->competitions = $repository->findCompetitionsParticipatesByUser($idUser);
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

}