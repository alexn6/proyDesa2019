<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;    // para incorporar servicios rest

use App\Entity\Deporte;
use App\Entity\Competencia;
use App\Entity\Resultado;

use App\Utils\Constant;

 /**
 * Predio controller
 * @Route("/api",name="api_")
 */
class ResultadoController extends AbstractFOSRestController
{
    /**
     * Lista de todos las predios.
     * @Rest\Get("/result/score"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function positionsTableFull(Request $request){
              
        $respJson = (object) null;
        $statusCode;
        $resultResp;

        $idCompetencia = $request->get('idCompetencia');
        $nroGrupo = $request->get('grupo');

        // ################ refactorizar la parte de la tabla por grupo
        
        // vemos si recibimos algun parametro
        if(!empty($idCompetencia)){
            $repositoryComp = $this->getDoctrine()->getRepository(Competencia::class);
            $competencia = $repositoryComp->find($idCompetencia);
            if($competencia != null){
                $codTipoOrg = $competencia->getOrganizacion()->getCodigo();
                // si es una eliminatoria devolvemos un mje indicando una redireccion
                if(($codTipoOrg == Constant::COD_TIPO_ELIMINATORIAS) || ($codTipoOrg == Constant::COD_TIPO_ELIMINATORIAS_DOUBLE)){
                    $resultResp = (object) null;
                    $resultResp->msg = "Las eliminatorias no cuentan con una tabla de posiciones";
                }
                // sino, es por fase de grupos, recuperamos la tabla de cada grupo
                else{
                    $repository = $this->getDoctrine()->getRepository(Resultado::class);
                    $resultados;

                    if(($codTipoOrg == Constant::COD_TIPO_LIGA_SINGLE) || ($codTipoOrg == Constant::COD_TIPO_LIGA_DOUBLE)){
                        $resultados = $repository->findResultCompetitors($idCompetencia);
                    }
                    // sino es por fase de grupos
                    else{
                        // recuperamos los resultados del grupo
                        $resultados = $repository->findResultCompetitorsGroup($idCompetencia, $nroGrupo);
                    }
                    if(count($resultados) == 0){
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $resultResp = (object) null;
                        $resultResp->msg = "No se han generados o disputados encuentros de la competencia.";
                    }
                    else{
                        // pasamos los resultado a un array para poder trabajarlo
                        $resultados = $this->get('serializer')->serialize($resultados, 'json', [
                            'circular_reference_handler' => function ($object) {
                                return $object->getId();
                            },
                            'ignored_attributes' => ['competencia', 'competidor']
                        ]);
                        // pasamos los reultados a un array para poder trabajarlos
                        $resultados = json_decode($resultados, true);
                        // buscamos los puntos por resultado segun deporte
                        $ptsByResult = $this->getPointsBySport($competencia);
                        // recuperamos la tabla de posiciones de la competencia
                        $diferenciaPts = $repository->getDiffCompetitors($idCompetencia, $nroGrupo);
                        $resultResp = $this->getTablePosition($resultados, $ptsByResult, $diferenciaPts);
                        // ordenamos los resultados segun puntos mas altos
                        usort($resultResp, function($a, $b ) {
                            return ($b['Pts'] - $a['Pts']);
                        });
                        
                        $statusCode = Response::HTTP_OK;
                    }
                }
                
            }
            else{
                $statusCode = Response::HTTP_OK;
                $resultResp->msg = "La competencia no existe o fue eliminada";
            }
        }
        else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            $resultResp->msg = "Solicitud mal formada. Faltan parametros.";
        }

        $resultResp = json_encode($resultResp);
        $response = new Response($resultResp);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }

    // ######################################################################################
    // ############################ funciones auxiliares ####################################

    // Pre: se controla que el deporte admita empates
    private function getTablePosition($resultCompetitors, $ptsByResult, $diffCompetitors){
        // recuperamos los puntos segun el deporte
        $ptsGanado = $ptsByResult["ganado"];
        $ptsEmpatado = $ptsByResult["empatado"];
        $ptsPerdido = $ptsByResult["perdido"];

        // calculamos los puntos de cada competidor
        for ($i=0; $i < count($resultCompetitors) ; $i++) {
            $pg = $resultCompetitors[$i]['PG'];
            $pe = $resultCompetitors[$i]['PE'];
            $pp = $resultCompetitors[$i]['PP'];
            // con esto hariamos un control mas si lo necesitaramos
            if($ptsEmpatado != null){
                if($pe == null){
                    $pe = 0;
                }
            }
            $ptsTotal = $pg*$ptsGanado + $pe*$ptsEmpatado + $pp*$ptsPerdido;
            
            $resultCompetitors[$i]['Pts'] = $ptsTotal;
            // asignamos la diferencia de anotaciones
            for ($j=0; $j < count($diffCompetitors); $j++) { 
                if($resultCompetitors[$i]['alias'] == $diffCompetitors[$j]->alias){
                    $resultCompetitors[$i]['Dif'] = $diffCompetitors[$j]->favor - $diffCompetitors[$j]->contra;
                }
            }
        }

        return $resultCompetitors;
    }

    // buscamos los puntos por resultado del encuentro segun el deporte
    private function getPointsBySport($competencia){
        $ptsByResults = array();
        $repositoryDep = $this->getDoctrine()->getRepository(Deporte::class);
        $pts = $repositoryDep->findPointByResultSport($competencia->getCategoria()->getDeporte()->getId());

        $pts = $this->get('serializer')->serialize($pts, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => ['competencia']
        ]);

        // pasamos los reultados a un array para poder trabajarlos
        $pts = json_decode($pts, true);
        $ptsByResults["ganado"] = $pts[0]['ppg'];
        $ptsByResults["empatado"] = $pts[0]['ppe'];
        $ptsByResults["perdido"] = $pts[0]['ppp'];
        
        return $ptsByResults;
    }

}