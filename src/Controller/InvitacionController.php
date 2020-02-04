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

use App\Utils\NotificationService;
use App\Utils\Constant;

    /**
     * UsuarioCompetencia controller
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
            $data = $repository->findBy(['usuarioDestino' => $user]);
            
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
   

}
