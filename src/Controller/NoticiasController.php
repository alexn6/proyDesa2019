<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Usuario;
use App\Entity\UsuarioCompetencia;
use App\Entity\Competencia;

use App\Model\Noticia;

use App\Utils\Constant;
use App\Utils\DbClodFirestoreManager;
use App\Utils\NotificationManager;

use Kreait\Firebase\Messaging\Notification;
use Google\Cloud\Core\Timestamp;

use \Datetime;

 /**
 * Usuario controller
 * @Route("/api/news",name="api_")
 */
class NoticiasController extends AbstractFOSRestController
{
    /**
     * Persiste una noticia en la db cloud, queda en la coleccion de la competencia correspodiente
     * @Rest\Post("/publish"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function publishNew(Request $request){

        $respJson = (object) null;
        $statusCode;

        if(empty($request->getContent())){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        }
        else{
            // recuperamos los datos del body y pasamos a un array
            $dataRequest = json_decode($request->getContent());
      
            if((isset($dataRequest->idCompetencia))&&(isset($dataRequest->idPublicador))&&(isset($dataRequest->titulo))&&(isset($dataRequest->resumen))&&(isset($dataRequest->descripcion))){
                // recuperamos el parametro recibido
                $idCompetencia = $dataRequest->idCompetencia;
        
                // buscamos la competencia
                $repository = $this->getDoctrine()->getRepository(Competencia::class);
                $competencia = $repository->find($idCompetencia);

                if($competencia == NULL){
                    $respJson->messaging = "La competencia no existe";
                    $statusCode = Response::HTTP_NO_CONTENT;
                }
                else{
                    $idPublicador = $dataRequest->idPublicador;
    
                    // buscamos la competencia
                    $repository = $this->getDoctrine()->getRepository(Usuario::class);
                    $usuario = $repository->find($idPublicador);

                    if($usuario == NULL){
                        $respJson->messaging = "El usuario publicador no existe";
                        $statusCode = Response::HTTP_NO_CONTENT;
                    }
                    else{
                        // informar a los usuarios SEG y COMP q se publico tal noticia
                        $this->sendNotificationPublishNews($competencia, $dataRequest->resumen);
                        // almacenamos la noticia en la db cloud
                        $this->publishNewCloud($competencia, $usuario, $dataRequest->titulo, $dataRequest->resumen, $dataRequest->descripcion);
                        $respJson->messaging = "Noticia publicada con exito.";
                        $statusCode = Response::HTTP_OK;
                    }
                }
            }
            else{
                $statusCode = Response::HTTP_BAD_REQUEST;
                $respJson->messaging = "Peticion mal formada. Faltan parametros.";
            }
        }
            
        $respJson = json_encode($respJson);

        $response = new Response($respJson);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);

        return $response;
    }

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
                $statusCode = Response::HTTP_NO_CONTENT;
            }
            else{
                // hacemos el string serializable , controlamos las autoreferencias
                $news = $this->lastedNews($idUser);
                if($news == NULL){
                    $respJson->messaging = "El usuario no aun no sigue ni participa en ninguna competencia.";
                    $statusCode = Response::HTTP_NO_CONTENT;
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


    // ###########################################################################
    // ######################### FUNCIONES PRIVADAS #########################

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

    // guardamos una noticia en la db nube de mi aplicacion
    private function publishNewCloud($competencia, $usuario, $title, $resume, $descripcion){
        $pathCollection = 'dbproyectotorneos/'.$competencia->getNombre().'/news';
        // $pathCollection = 'news-test/comp-test/news';
        $cantNews = DbClodFirestoreManager::getInstance()->sizeCollection($pathCollection);
        $documentId = $cantNews + 1;
        if($cantNews >= Constant::CANT_MAX_NOTICIAS_CLOUD){
            // borramos la noticia mas antigua y guardamos la nueva
        }
        // creamos los datos de la noticia
        $data = ['title' => $title, 
                    'resume' => $resume, 
                    'descripcion' => $descripcion,
                    'uptime' => new Timestamp(new DateTime()),
                    'publisher' => $usuario->getNombreUsuario()
                ];

        DbClodFirestoreManager::getInstance()->insertDocument($pathCollection, $data, $documentId);
    }

    // Mandamos la notificacion del cambio de estado de la competencia
  private function sendNotificationPublishNews($competencia, $resumenNoticia){
    $nameComp = str_replace(' ', '', $competencia->getNombre());

    $topicFollowers = $nameComp. '-' .Constant::ROL_SEGUIDOR;
    $topicCompetitors = $nameComp. '-' .Constant::ROL_COMPETIDOR;

    $title = 'Competencia: '.$competencia->getNombre();
    
    $notification = Notification::create($title, $resumenNoticia);

    NotificationManager::getInstance()->notificationToTopic($topicFollowers, $notification);
    NotificationManager::getInstance()->notificationToTopic($topicCompetitors, $notification);
  }

}