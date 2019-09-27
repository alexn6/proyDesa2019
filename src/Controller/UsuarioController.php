<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Usuario;

 /**
 * Usuario controller
 * @Route("/api",name="api_")
 */
class UsuarioController extends AbstractFOSRestController
{

  private $passwordEncoder;

  /**
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

  /**
   * name es solo para sermapeada en el archivo de config
  * @Route("/api/users", name="serviceUser")
  */
/*   public function allUsers()
  {
    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();

    // hacemos el string serializable
    $users = $this->get('serializer')->serialize($users, 'json');

    // $response = new Response($users);
    $response = new Response("algo de users");
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  } */

  /**
     * Lista de todos los deportes.
     * @Rest\Get("/users"), defaults={"_format"="json"})
     * 
     * @return Response
     */
  public function getUsers(){
    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();


    // hacemos el string serializable , controlamos las autoreferencias
    $users = $this->get('serializer')->serialize($users, 'json');

    $response = new Response($users);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }

  /**
     * Crea un usuario.
     * @Rest\Post("/user"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function create(Request $request){

      $respJson = (object) null;
      $statusCode;
      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Usuario::class);
  
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());      
        var_dump($dataUserRequest);

        $nombreUsuario = $dataUserRequest->usuario;
        $correo = $dataUserRequest->correo;
          
        // controlamos que el nombre de usuario este disponible
        $usuario = $repository->findOneBy(['nombreUsuario' => $nombreUsuario]);
        if($usuario){
          $respJson->success = false;
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "El nombre de usuario esta en uso";
        }
  
        // controlamos que el correo no este en uso
        $usuario_correo = $repository->findOneBy(['correo' => $correo]);
        if($usuario_correo){
          $respJson->success = false;
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "El correo esta en uso por una cuenta existente";
        }
  
        if((!$usuario)&&(!$usuario_correo)){
          // creamos el usuario
          $usuarioCreate = new Usuario();
          $usuarioCreate->setNombre($dataUserRequest->usuario);
          $usuarioCreate->setApellido($dataUserRequest->apellido);
          $usuarioCreate->setNombreUsuario($nombreUsuario);
          $usuarioCreate->setCorreo($correo);
          $usuarioCreate->setPass($dataUserRequest->pass);
  
          // encriptamos la contraseña
          $passHash = $this->passwordEncoder->encodePassword($usuarioCreate, $usuarioCreate->getNombre());
          $usuarioCreate->setPass($passHash);
  
          $em = $this->getDoctrine()->getManager();
          $em->persist($usuarioCreate);
          $em->flush();
  
          $statusCode = Response::HTTP_CREATED;
  
          $respJson->success = true;
          $respJson->messaging = "Creacion exitosa";
        }
      }
      else{
        $respJson->success = false;
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada";
      }

      
      $respJson = json_encode($respJson);

      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);

      return $response;
  }
}