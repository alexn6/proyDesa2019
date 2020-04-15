<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Utils\Constant;

 /**
 * Edicion controller
 * @Route("/api",name="api_")
 */
class EdicionController extends AbstractFOSRestController
{
    /**
     * Lista de todos las predios.
     * @Rest\Get("/grounds"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function allGrounds(){
        $respJson =  null;
        $repository = $this->getDoctrine()->getRepository(Predio::class);
        $grounds = $repository->findall();

        $grounds = $this->get('serializer')->serialize($grounds, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => ['competencia', 'prediocompetencia']
        ]);
        // pasamos a un array para procesarlo
        $array_comp = json_decode($grounds, true);
        // cambiamos el objeto deporte por su nombre
        foreach ($array_comp as &$valor) {
            $valor['ciudad'] = $valor['ciudad']['nombre'];      
        }
        if(empty($array_comp)){
            $respJson = (object) null;
            $respJson->messaging = "No se encontraron ciudades";
            $respJson = json_encode($respJson);
            $statusCode = Response::HTTP_BAD_REQUEST;
        }else{
            $respJson = json_encode($array_comp);  
            $response = new Response($respJson);
            $statusCode = Response::HTTP_OK;
        }

        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Crea un predio.
     * @Rest\Post("/grounds"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
  
          $repository = $this->getDoctrine()->getRepository(Predio::class);
          $repositoryCity = $this->getDoctrine()->getRepository(Ciudad::class);

          // recuperamos los datos del body y pasamos a un array
          $dataPredioRequest = json_decode($request->getContent());
          
          if(!$this->correctDataCreate($dataPredioRequest)){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";
          }
          else{
              // recuperamos los datos del body
              $nombre = $dataPredioRequest->nombre;
            //   $idCompetencia = $dataPredioRequest->idCompetencia;
              $direccion = $dataPredioRequest->direccion;
              $ciudad = $repositoryCity->find($dataPredioRequest->ciudad);
                
              // controlamos que el nombre de predio este disponible
              $predio = $repository->findOneBy(['nombre' => $nombre]);
              if($predio){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "El nombre del predio esta en uso";
              }
              else{
                    // creamos el predio
                    $newPredio = new Predio();
                    $newPredio->setNombre($nombre);
                    $newPredio->setDireccion($direccion);
                    $newPredio->setCiudad($ciudad);
            
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newPredio);
                    $em->flush();
            
                    $statusCode = Response::HTTP_CREATED;
                    $respJson->messaging = "Creacion exitosa";
              }
          }
        }
        else{
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Peticion mal formada";
        }
        
        $respJson = json_encode($respJson);
  
        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Elimina un predio
     * @Rest\Delete("/grounds/del"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function delete(Request $request){

        $respJson = (object) null;
        $statusCode;

        $idCompetencia = $request->get('idCompetencia');
        $idPredio = $request->get('idPredio');
      
        // vemos si recibimos el id de un predio para eliminarlo
        if(empty($idCompetencia)){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada.";
        }else{
            if(empty($idPredio)){
                $respJson->success = false;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
            else{            
                $repository=$this->getDoctrine()->getRepository(Predio::class);
                $predio = $repository->findGroundsByCompetetition($idCompetencia,$idPredio);
                if($predio == NULL){
                    $respJson->success = true;
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "El predio y/o competencia incorrecta o inexistente";
                }
                else{
                    // ######################################################################
                    // tmb deberiamos borrar todos los campos de este predio
                    // ######################################################################
                    // $urlDeleteCampo = Constant::SERVICES_REST_CAMPO.'/fields/del?idPredio='.$idPredio;
                    // $client = HttpClient::create();
                    // $response = $client->request('DELETE', $urlDeleteCampo);
                    //$statusCodeDelete = $response->getStatusCode();

                    $repositoryCampos = $this->getDoctrine()->getRepository(Campo::class);
                    $campos = $repositoryCampos->findFieldsByGrounds($idPredio,null);
                    // eliminamos los campos del predio
                    $this->forward('App\Controller\CampoController::deleteSetCampus', [
                        'campos' => $campos
                    ]);

                    // eliminamos el dato y refrescamos la DB
                    $em = $this->getDoctrine()->getManager();
                    foreach ($predio as $predios) {
                        $em->remove($predios);
                    }
                    $em->flush();
        
                    $respJson->success = true;
                    $statusCode = Response::HTTP_OK;
                    $respJson->messaging = "Eiminacion correcta";
                }
            }
        
        }
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
  
        return $response;
    }

    /**
     * Lista de todos los predios que contengan el nombre recibido por parametro
     * @Rest\Get("/grounds/name"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getGroundsLikeName(Request $request){
        $respJson = (object) null;
        $grounds = null;
        $statusCode;
        $repository = $this->getDoctrine()->getRepository(Predio::class);
  
        $nombre = null;

        if(!empty($request->get('nombre'))){
            $nombre = $request->get('nombre');
        }
    
        $grounds = $repository->getLikeName($nombre);

        // hacemos el string serializable , controlamos las autoreferencias
        $grounds = $this->get('serializer')->serialize($grounds, 'json', [
        'circular_reference_handler' => function ($object) {
            return $object->getId();
        },
        'ignored_attributes' => ['prediocompetencia']
        ]);
        // pasamos solo el nombre de la ciudad
        $grounds = json_decode($grounds);
        foreach ($grounds as &$ground) {
            $ground->ciudad = $ground->ciudad->nombre;
        }

        $respJson = $grounds;

        $statusCode = Response::HTTP_OK;
        $respJson = json_encode($respJson);
        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
      }

    // ######################################################################################
    // ############################ funciones auxiliares ####################################

    // controlamos que los datos recibidos esten completos
    private function correctDataCreate($dataRequest){
        if(!property_exists((object) $dataRequest,'nombre')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'direccion')){
            return false;
        }
        if(!property_exists((object) $dataRequest,'ciudad')){
            return false;
        }
        return true;
    }


}