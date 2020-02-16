<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Usuario;
use App\Entity\UsuarioCompetencia;

use App\Model\Noticia;

use App\Utils\Constant;
use App\Utils\DbClodFirestoreManager;

 /**
 * Usuario controller
 * @Route("/api/news",name="api_")
 */
class NoticiasController extends AbstractFOSRestController
{
    /**
     * Recupera como maximo las 50 ultimas noticias de las competencias de las que el
     * usuario es participe como SEGUIDOR o COMPETIDOR
     * @Rest\Get("/competitions"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function competitionsParticipe(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(empty($request->get('idUsuario'))){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
        else{
            // recuperamos el parametro recibido
            $idUser = $request->get('idUsuario');
    
            // buscamos el usuario
            $repository = $this->getDoctrine()->getRepository(Usuario::class);
            $usuario = $repository->find($idUser);

            if($usuario == NULL){
                $respJson->messaging = "El usuario no existe";
                $statusCode = Response::HTTP_OK;
            }
            else{
                // hacemos el string serializable , controlamos las autoreferencias
                $news = $this->lastedNews($idUser);
                if($news == NULL){
                    $respJson->messaging = "El usuario no aun no sigue ni participa en ninguna competencia.";
                    $statusCode = Response::HTTP_OK;
                }
                else{
                    $news = $this->get('serializer')->serialize($news, 'json');
                    $news = json_decode($news);
                    //$respJson->news = $news;
                    $respJson = $news;
                    $statusCode = Response::HTTP_OK;
                }
            }
        }
            
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }



    // recupera las ultimas noticias de las competencias en las q forma parte el usuario
    // @return null si no existen competencias
    private function lastedNews($idUser){
        $repositoryUserComp = $this->getDoctrine()->getRepository(UsuarioCompetencia::class);
        $namesCompetitions = $repositoryUserComp->namesCompetitionsParticipe($idUser);

        if(count($namesCompetitions) == 0){
            return null;
        }

        $newsAllCompetitions = array();

        // recuperamos las ultimas noticias de cada competencia y las juntamos en un array
        foreach ($namesCompetitions as $name) {
            //var_dump($name);
            $newsCompetition = $this->getLastedNewsCompetition($name['nombre']);
            $newsAllCompetitions = array_merge($newsAllCompetitions, $newsCompetition);
        }

        //var_dump($newsCompetition);

        // recuperamos nada mas que los datos de las noticias de nuestro interes
        $newsAllCompetitions = $this->getDataDocuments($newsAllCompetitions);

        // ordenamos las noticias por fecha
        usort($newsAllCompetitions, function($a, $b ) {
            return strtotime($b->getUptime()) - strtotime($a->getUptime());
        });

        // controlamos que no superen las n max noticias
        if(count($newsAllCompetitions) > Constant::CANT_MAX_NOTICIAS){
            return $newsAllCompetitions = array_slice($newsAllCompetitions, 0, Constant::CANT_MAX_NOTICIAS);
        }
        
        return $newsAllCompetitions;
        //return null;
    }

    // recuperamos las ultimas n noticias de una competencia
    private function getLastedNewsCompetition($nameCompetition){
        //var_dump($nameCompetition);
        $pathCollection = 'dbproyectotorneos/'.$nameCompetition.'/news';
        $field = 'uptime';  // ordenamos por fecha de subida
        $n = 4;
        return DbClodFirestoreManager::getInstance()->getLastednDocument($pathCollection, $field, $n);
    }

    // recupera lña informacion de interes de la noticia, solo la necesaria ar mostrar
    private function getDataDocuments($arrayDocuments){
        $arraydata = array();

        // recorremos cada uno de los DocumentSnapshot
        foreach ($arrayDocuments as $document) {
            $arrayFields = $document->data();
            $nameCompetition = $this->nameCompetitionNews($document);
            $date = $arrayFields['uptime']->get('date');
            //var_dump($date);
            $dataObjectNew = new Noticia($document->id(), $nameCompetition, $arrayFields['title'], $arrayFields['resume'], $arrayFields['descripcion'], $arrayFields['uptime']);
            array_push($arraydata, $dataObjectNew);
        }

        return $arraydata;
    }

    // recuperamos el nombre de la competencia del path de la coleccioin
    private function nameCompetitionNews($document){
        $pathCollection = $document->reference()->parent()->path();
        $subPath = explode("/", $pathCollection);

        return $subPath[1];
    }

}