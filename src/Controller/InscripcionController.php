<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use \Datetime;

use App\Entity\Inscripcion;
use App\Entity\Competencia;

use App\Utils\Constant;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class InscripcionController extends AbstractFOSRestController
{

    /**
     * Crea una inscripcion.
     * @Rest\Post("/inscription"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

        $respJson = (object) null;
        $statusCode;

        // recuperamos los datos del body y pasamos a un array
        $dataRequest = json_decode($request->getContent());

        if(!$this->correctDataCreate($dataRequest)){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros o cuentan con nombres erroneos.";    
        }
        else{
            // recuperamos los datos del body
            $idCompetencia = $dataRequest->idCompetencia;
            $fechaInicio = $dataRequest->fechaInicio;
            $fechaCierre = $dataRequest->fechaCierre;

            $monto = null;
            $requisitos = null;
            if(isset($dataRequest->monto)){
                $monto = $dataRequest->monto;
            }
            if(isset($dataRequest->requisitos)){
                $requisitos = $dataRequest->requisitos;
            }

            // verificamos la existencia de la competencia
            $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
            $competencia = $repositoryComp->find($idCompetencia);

            if($competencia == null){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Competencia inexistente";
            }
            else{
                // controlamos que la competencia no cuente con una inscripcion
                $inscripcion = $competencia->getInscripcion();

                if($inscripcion != null){
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "La competencia ya cuenta con una inscripcion.";
                }
                else{
                    $fechaInicio = DateTime::createFromFormat(Constant::FORMAT_DATE, $fechaInicio);
                    $fechaCierre = DateTime::createFromFormat(Constant::FORMAT_DATE, $fechaCierre);
                    // TODO: aca iria el control de las fechas, q u
                    if($this->dateCorrect($fechaInicio, $fechaCierre)){
                        // creamos la inscripcion
                        $nuevainscripcion = new Inscripcion();
                        $nuevainscripcion->setFechaIni($fechaInicio);
                        $nuevainscripcion->setFechaCierre($fechaCierre);

                        if($monto != null){
                            $nuevainscripcion->setMonto($monto);
                        }
                        if($requisitos != null){
                            $nuevainscripcion->setRequisitos($requisitos);
                        }

                        // seteamos el esatdo de la competencia
                        $competencia->setEstado(Constant::ESTADO_COMP_INSCRIPCION_ABIERTA);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($nuevainscripcion);
                        $em->flush();

                        // asignamos la inscricion a la competencia
                        $competencia->setInscripcion($nuevainscripcion);
                        $em->flush();
                
                        $statusCode = Response::HTTP_CREATED;
                        $respJson->messaging = "Creacion exitosa";
                    }
                    else{
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $respJson->messaging = "Verifique la consistencia de las fechas. Controle que la feche de cierre sea al menos un dia posterior a la fecha de inicio";
                    }
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
     * Devuelve todas los predios de una competencia
     * @Rest\Get("/inscription-competition"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getInscriptionByCompetition(Request $request){
        $repository = $this->getDoctrine()->getRepository(Inscripcion::class);

        $respJson = (object) null;

        $idCompetencia = $request->get('idCompetencia');
        $inscription  = NULL;

        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
            $competencia = $repositoryComp->find($idCompetencia);
        
            if($competencia == null){
                $inscription  = NULL;
                
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "No existe competencia.";
            }else{
                if($competencia->getInscripcion() != null){
                    $inscription = $competencia->getInscripcion();
                    $inscription = $this->get('serializer')->serialize($inscription, 'json', [
                        'circular_reference_handler' => function ($object) {
                            return $object->getId();
                        },
                        'ignored_attributes' => ['competencia', '__initializer__', '__cloner__', '__isInitialized__']
                    ]);
                        var_dump($inscription);
                    $respJson = $inscription;
                    $statusCode = Response::HTTP_OK;    
                }
                else {
                    $inscription  = NULL;
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->messaging = "Competencia sin inscripcion.";        
                }
            }
        }
        else{
            $inscription  = NULL;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
        
        $respJson = json_encode($inscription);
        $respJson = json_decode($respJson);

        $response = new Response($respJson);
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
    
        return $response;
    }
    
    // ######################################################################################
    // ############################ funciones auxiliares ####################################

    // controlamos que los datos recibidos esten completos
    private function correctDataCreate($dataRequest){
        if((isset($dataRequest->idCompetencia))&&(isset($dataRequest->fechaInicio))&&(isset($dataRequest->fechaCierre))){
            return true;
        }
        return false;
    }

    // verifica que la fecha de cierre sea mayor q la de inicio y q ambas sean mayor a la actual
    private function dateCorrect($fechaInicio, $fechaCierre){
        // verificamos que ambas fechas sean posteriores al dia de la fecha
        if($this->datePostCurrent($fechaInicio) && $this->datePostCurrent($fechaCierre)){
            // controlamos que la feche de cierre sea al menos un dia depues de la fecha de inicio
            return $this->diffDateCorrect($fechaCierre, $fechaInicio, 1);
        }
        // $this->datePostCurrent($fechaInicio);
    }

    // verifica q se asegure una cant minima de dias entre las fechas
    private function diffDateCorrect($f1, $f2, $n){
        $diff = date_diff($f2, $f1);
        $array_diff = str_split($diff->format("%R%a"));
        if($array_diff[0] == '+'){
            if($array_diff[1] >= $n){
                // var_dump(true);
                return true;
            }
        }
        // var_dump(false);
        return false;
    }

    // verifica q se asegure una cant minima de dias entre las fechas
    private function datePostCurrent($f1){
        $now = new DateTime();  // fecha actual
        $diff = date_diff($now, $f1);
        if($f1->format('Y-m-d') == $now->format('Y-m-d')){
            // var_dump(true);
            return true;
        }
        $array_diff = str_split($diff->format("%R%a"));
        if($array_diff[0] == '+'){
            // var_dump(true);
            return true;
        }
        // var_dump(false);
        return false;
    }
}