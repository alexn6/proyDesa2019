<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Utils\GeneratorEncuentro;
use App\Utils\CreatorEncuentros;
use App\Utils\Constant;
use App\Entity\Competencia;
use App\Entity\UsuarioCompetencia;
use App\Entity\TipoOrganizacion;
use App\Entity\Encuentro;

use App\Controller\EncuentroController;
use App\Utils\Validation;
use App\Utils\Reflection;

 /**
 * Usuario controller
 * @Route("/api/generator",name="api_")
 */
class GeneradorEncuentroController extends AbstractFOSRestController
{

    private $generator;

    
    public function __construct(GeneratorEncuentro $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generacion de encuentros LIGA SINGLE
     * @Rest\Post("/league-single"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function generateMatchesLeagueSingle(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
            // recuperamos los datos del body y pasamos a un array
            $competitorsBody = json_decode($request->getContent(), true);
            if(count($competitorsBody) < 2){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->matches = NULL;
                $respJson->messaging = "La cantidad de competidores es insuficiente";    
            }
            else{
                $matchesCompetition = $this->generator::ligaSingle($competitorsBody);
                //var_dump($matchesCompetition);
    
                $statusCode = Response::HTTP_OK;
                $respJson->matches = $matchesCompetition;
                $respJson->messaging = "Generacion realizada con exito";
            }

        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->matches = NULL;
            $respJson->messaging = "Peticion mal formada";
        }
          
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }

    /**
     * Generacion de encuentros LIGA DOUBLE
     * @Rest\Post("/league-double"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function generateMatchesLeagueDouble(Request $request){

        $respJson = (object) null;
        $statusCode;

        // vemos si existe un body
        if(!empty($request->getContent())){
            // recuperamos los datos del body y pasamos a un array
            $competitorsBody = json_decode($request->getContent(), true);
            if(count($competitorsBody) < 2){
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->matches = NULL;
                $respJson->messaging = "La cantidad de competidores es insuficiente";    
            }
            else{
                $matchesCompetition = $this->generator::ligaDouble($competitorsBody);
                //var_dump($matchesCompetition);

                $statusCode = Response::HTTP_OK;
                $respJson->matches = $matchesCompetition;
                $respJson->messaging = "Generacion realizada con exito";
            }
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->matches = NULL;
            $respJson->messaging = "Peticion mal formada";
        }
    
          
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }


    /**
     * 
     * @Rest\Get("/league-single-id")
     * Por nombre de competencia
     * 
     * @return Response
     */
    public function generateMatchesLeagueSingleByCompetition(Request $request){
        $nameCompetition = $request->get('competencia');
        $repository = $this->getDoctrine()->getRepository(Competencia::class);
        $repositoryUsComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);

        $respJson = (object) null;
        $statusCode;
       
        // vemos si recibimos algun parametro
        if(!empty($nameCompetition)){
            $competition = $repository->findOneBy(['nombre' => $nameCompetition]);

            if(empty($competition)){
                $respJson->matches = NULL;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->msg = "La competencia no existe o fue eliminada";
            }
            else{
                $idCompetencia = $competition->getId();
                // recuperamos los usuario_competencia de una competencia
                $participantes = $repositoryUsComp->findParticipanteByCompetencia($idCompetencia);
    
                if(count($participantes) < 2){
                    $respJson->matches = NULL;
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->msg = "No cuenta con suficientes participantes para crear la competencia";
                }
                else{
                    $nombre_participantes = array();
                    foreach ($participantes as &$valor) {
                        array_push($nombre_participantes, $valor['nombreUsuario']);
                    }
    
                    $matchesCompetition = $this->generator::ligaSingle($nombre_participantes);
    
                    $statusCode = Response::HTTP_OK;
                    $respJson->msg = "Generacion realizada con exito";
                    $respJson->matches = $matchesCompetition;
                }
    
                // var_dump($participantes);
    
                $statusCode = Response::HTTP_OK;
            }
        }
        else{
            $respJson->matches = NULL;
            $respJson->msg = "Solicitud mal formada";
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * 
     * @Rest\Get("/league-double-id")
     * Por nombre de competencia
     * 
     * @return Response
     */
    public function generateMatchesLeagueDoubleByCompetition(Request $request){
        $nameCompetition = $request->get('competencia');
        $repository = $this->getDoctrine()->getRepository(Competencia::class);
        $repositoryUsComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);

        $respJson = (object) null;
        $statusCode;
       
        // vemos si recibimos algun parametro
        if(!empty($nameCompetition)){
            $competition = $repository->findOneBy(['nombre' => $nameCompetition]);

            if(empty($competition)){
                $respJson->matches = NULL;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->msg = "La competencia no existe o fue eliminada";
            }
            else{
                $idCompetencia = $competition->getId();
                // recuperamos los usuario_competencia de una competencia
                $participantes = $repositoryUsComp->findParticipanteByCompetencia($idCompetencia);
    
                if(count($participantes) < 2){
                    $respJson->matches = NULL;
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $respJson->msg = "No cuenta con suficientes participantes para crear la competencia";
                }
                else{
                    $nombre_participantes = array();
                    foreach ($participantes as &$valor) {
                        array_push($nombre_participantes, $valor['nombreUsuario']);
                    }
    
                    $matchesCompetition = $this->generator::ligaDouble($nombre_participantes);
    
                    $statusCode = Response::HTTP_OK;
                    $respJson->msg = "Generacion realizada con exito";
                    $respJson->matches = $matchesCompetition;
                }
    
                // var_dump($participantes);
    
                $statusCode = Response::HTTP_OK;
            }
        }
        else{
            $respJson->matches = NULL;
            $respJson->msg = "Solicitud mal formada";
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * 
     * @Rest\Get("/matches")
     * Genera los encuentros de una competencia, si cuenta on los competidores suficiente
     * 
     * @return Response
     */
    public function generateMatchesCompetition(Request $request){
        $idCompetition = $request->get('idCompetencia');
        $repositoryEnc = $this->getDoctrine()->getRepository(Encuentro::class);
        $respJson = (object) null;
        $statusCode;
       
        // vemos si recibimos algun parametro
        if(!empty($idCompetition)){
            $repository = $this->getDoctrine()->getRepository(Competencia::class);
            $competition = $repository->find($idCompetition);

            if(empty($competition)){
                $respJson->matches = NULL;
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->msg = "La competencia no existe o fue eliminada";
            }
            else{
                $repositoryUsComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
                $idCompetencia = $competition->getId();
                // recuperamos los usuario_competencia de una competencia
                $participantes = $repositoryUsComp->findAliasCompetidoresByCompetencia($idCompetencia);
                $comprobar = $repositoryEnc->findOneBy(['competencia' => $competition]);

                if(!empty($comprobar)){
                    $respJson->matches = NULL;
                    $respJson->msg = "Encuentros ya generados";
                    $statusCode = Response::HTTP_BAD_REQUEST; 
                }else{
                    $cantCorrecta = $this->validarCantCompetidores($competition, $participantes);
                    if($cantCorrecta !== true){
                        $respJson->matches = NULL;
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $respJson->msg = "Verifique la cantidad de comopetidores. Cantidad minima: ".$cantCorrecta;
                    }
                    else{
                        // recuperamos la lista de los nombres de los participantes
                        $nombre_participantes = array();
                        foreach ($participantes as &$valor) {
                            array_push($nombre_participantes, $valor['alias']);
                        }

                        // recuperamos el tipo de org de la competencia
                        $repositoryTypeorg = $this->getDoctrine()->getRepository(TipoOrganizacion::class);
                        $tipoorg = $repositoryTypeorg->find($competition->getOrganizacion());
                        $codigo_tipo = $tipoorg->getCodigo();
        
                        $generatorMatches = new CreatorEncuentros();
                        if(strcmp($codigo_tipo, "FASEGRUP") == 0){
                            $matchesCompetition = $generatorMatches->createMatches($nombre_participantes, $codigo_tipo, $competition->getCantGrupos());
                        }
                        else{
                            $matchesCompetition = $generatorMatches->createMatches($nombre_participantes, $codigo_tipo, null);
                        }
                        // actualizamos el estado de la competencia
                        if($competition->getEstado() != Constant::ESTADO_COMP_INSCRIPCION_CERRADA){
                            $competition->setEstado(Constant::ESTADO_COMP_INSCRIPCION_CERRADA); 
                        }
                        
                        // vamos a persistir los datos de los encuentros generados
                        $this->forward('App\Controller\EncuentroController::saveFixture', [
                            'matches'  => $matchesCompetition,
                            'competencia' => $competition,
                            'tipoorg' => $codigo_tipo
                        ]);
                        $statusCode = Response::HTTP_OK;
                        $respJson->msg = "Generacion realizada con exito";
                        $respJson->matches = $matchesCompetition;
                    }
                
                }
            }
        }
        else{
            $respJson->matches = NULL;
            $respJson->msg = "Solicitud mal formada";
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    // #####################################################################################
    // ################# validar cant minima de competencia ################################

    // controlamos que la cant de competidores se adecue a la competencia
    private function validarCantCompetidores($competencia, $competidores){
        // aca sacamos la funcion de control de cada tipo de organizacion
        $funcion_validacion = $competencia->getOrganizacion()->getMinimo();
        // recuperamos los datos a pasarle a la funcion
        $n_competidores = count($competidores);
        $n_fase = $competencia->getFase();
        $n_grupos = $competencia->getCantGrupos();
        $n_minima = $competencia->getMinCompetidores();
        
        $validator = new Validation();
        $managerFunction = new Reflection();
        // recuperamos la funcion del objeto y la ejecutamos
        $exec_function = $managerFunction->getFunction($validator, $funcion_validacion);
        $rdo_validation = $exec_function($n_fase, $n_minima, $n_grupos, $n_competidores);

        return $rdo_validation;
    }

    // private function validarCompetidoresEliminitorias($n_fase, $n_minima, $n_grupos, $n_competidores){

    // }

    // private function validarCompetidoresLiga($n_fase, $n_minima, $n_grupos, $n_competidores){

    // }

    // private function validarCompetidoresGrupos($n_fase, $n_minima, $n_grupos, $n_competidores){

    // }
}