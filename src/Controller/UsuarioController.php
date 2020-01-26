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
     * Lista de todos los deportes.
     * @Rest\Get("/users"), defaults={"_format"="json"})
     * 
     * @return Response
     */
  public function getUsers(){
    $repository=$this->getDoctrine()->getRepository(Usuario::class);
    $users=$repository->findall();

    // hacemos el string serializable , controlamos las autoreferencias
    $users = $this->get('serializer')->serialize($users, 'json', [
      'circular_reference_handler' => function ($object) {
        return $object->getId();
      },
      'ignored_attributes' => ['usuarioscompetencias', 'roles', 'password', 'pass', 'salt']
    ]);

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
        // var_dump($dataUserRequest);

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
          $usuarioCreate->setNombre($dataUserRequest->nombre);
          $usuarioCreate->setApellido($dataUserRequest->apellido);
          $usuarioCreate->setNombreUsuario($nombreUsuario);
          $usuarioCreate->setCorreo($correo);
          $usuarioCreate->setPass($dataUserRequest->pass);
          $usuarioCreate->setToken($dataUserRequest->token);
  
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

  /**
     * Controla el iniicio de sesion de un usuario
     * @Rest\Post("/user/singin"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function singin(Request $request){

      $respJson = (object) null;
      $statusCode;

      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Usuario::class);
  
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());
        
          // recuperamos los datos del body
          $usuario = $dataUserRequest->usuario;
          $pass = $dataUserRequest->pass;
            
          // controlamos que el usuario exista
          $repositoryComp = $this->getDoctrine()->getRepository(Usuario::class);

          // buscamos el usuario por los 2 campos
          $nombreUsuarioDB = $repository->findOneBy(['nombreUsuario' => $usuario]);
          $correoDB = $repository->findOneBy(['correo' => $usuario]);

          // si no encontramos un usuario con el nombre de usuario pasamos a probar con el correo
          if(($nombreUsuarioDB == NULL) && ($correoDB == NULL)){
            // $respJson->success = false;
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Usuario inexistente";
          }
          else{
            // recuperamos el usuario
            if($nombreUsuarioDB != NULL){
              $usuarioDB = $nombreUsuarioDB;
            }
            else{
              $usuarioDB = $correoDB;
            }
            // TODO: controlamos si la contraseña es correcta
            if($this->passwordEncoder->isPasswordValid($usuarioDB, $pass)){
              $statusCode = Response::HTTP_OK;
              // $respJson->success = true;
              $respJson->messaging = "Inicio de sesion exitoso";
            }
            else{
              $statusCode = Response::HTTP_BAD_REQUEST;
              // $respJson->success = false;
              $respJson->messaging = "La contraseña no es correcta";
            }
          }
      }
      else{
        // $respJson->success = false;
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
     * Actualiza el rol de usuario_competencia
     * @Rest\Put("/user-token"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function updateToken(Request $request){

      $respJson = (object) null;
      $statusCode;

      if(!empty($request->getContent())){

        // recuperamos los datos del body y pasamos a un array
        $dataRequest = json_decode($request->getContent());

        if((!empty($dataRequest->nombreUsuario))&&(!empty($dataRequest->token))){
          // vemos si existen los datos necesarios
          $nombreUsuario = $dataRequest->nombreUsuario;
          $new_token = $dataRequest->token;
          
          // buscamos el usuario
          $repository=$this->getDoctrine()->getRepository(Usuario::class);
          $usuario = $repository->findOneBy(['nombreUsuario' => $nombreUsuario]);

          if($usuario != NULL){
            $em = $this->getDoctrine()->getManager();
            $usuario->setToken($new_token);
            $em->flush();
    
            $respJson->messaging = "Actualizacion realizada";
          }
          else{
            $respJson->messaging = "El usuario no existe";
          }
          $statusCode = Response::HTTP_OK;
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
     * Lista de todos los usuarios que contengan el nombre de usuario pasado por parametro.
     * @Rest\Get("/users/getUsersByUsername"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function getUsersByUsername(Request $request){
      $respJson = (object) null;
      $users = null;
      $statusCode;
      $repository=$this->getDoctrine()->getRepository(Usuario::class);

      if(empty($request->get('username'))){
        $respJson->success = false;
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada. Faltan parametros.";
        
      }else{
        $username = $request->get('username');
        $statusCode = Response::HTTP_OK;
      
        $users=$repository->getUsersByUsername($username);
  
        // hacemos el string serializable , controlamos las autoreferencias
        $users = $this->get('serializer')->serialize($users, 'json', [
          'circular_reference_handler' => function ($object) {
            return $object->getId();
          },
          'ignored_attributes' => ['usuarioscompetencias', 'token', 'roles', 'password', 'pass', 'salt']
        ]);
        $respJson = json_decode($users);
      }

      $respJson = json_encode($respJson);
       
      $response = new Response($respJson);
      $response->setStatusCode($statusCode);
      $response->headers->set('Content-Type', 'application/json');
  
      return $response;
    }

}