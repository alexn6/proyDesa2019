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
     * Lista de todos los usuarios.
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
     * Actualiza los datos de un usuario
     * @Rest\Put("/user"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function updateUser(Request $request){
      $respJson = (object) null;

      // vemos si existe un body
      if(!empty($request->getContent())){
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());      
        // buscamos el usuario
        $idUser = $dataUserRequest->id;
        $repository = $this->getDoctrine()->getRepository(Usuario::class);
        $user = $repository->find($idUser);

        // vemos si existe el usuario para actualizar sus datos
        if($user == NULL){
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "Usuario inexistente";
        }
        else{
          // controlamos que el nombre de usuario y el correo este disponible
          $userNombreUsuario = $repository->findOneBy(['nombreUsuario' => $dataUserRequest->usuario]);
          $userCorreo = $repository->findOneBy(['correo' => $dataUserRequest->correo]);

          if(($userNombreUsuario == NULL)&&($userCorreo == NULL)){
            //actualizamos los datos del usuario
            $user->setNombre($dataUserRequest->nombre);
            $user->setApellido($dataUserRequest->apellido);
            $user->setCorreo($dataUserRequest->correo);
            $user->setNombreUsuario($dataUserRequest->usuario);
    
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
    
            $statusCode = Response::HTTP_OK;
            // TODO: devolver el usuario para la app android
            // $respJson->messaging = "Actualizacion exitosa";
            $respJson->id = $user->getId();
            $respJson->nombre = $user->getNombre();
            $respJson->apellido = $user->getApellido();
            $respJson->correo = $user->getCorreo();
            $respJson->usuario = $user->getNombreUsuario();
          }
          else{
            $statusCode = Response::HTTP_BAD_REQUEST;
            if($userNombreUsuario != NULL){
              $respJson->messaging = "El nombre de usuario no esta disponible. Intente con otro.";
            }
            else{
              $respJson->messaging = "El correo no esta disponible. Intente con otro.";
            }
          }

        }
      }
      else{
        $statusCode = Response::HTTP_BAD_REQUEST;
        $respJson->messaging = "Peticion mal formada. Faltan parametros.";
      }
  
      $respJson = json_encode($respJson);
      $response = new Response($respJson);
      $response->headers->set('Content-Type', 'application/json');
      $response->setStatusCode($statusCode);
  
      return $response;
    }

  /**
     * Registra un nuevo usuario.
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
          $statusCode = Response::HTTP_BAD_REQUEST;
          $respJson->messaging = "El nombre de usuario esta en uso";
        }
  
        // controlamos que el correo no este en uso
        $usuario_correo = $repository->findOneBy(['correo' => $correo]);
        if($usuario_correo){
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
          $respJson->messaging = "Creacion exitosa";
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
            // controlamos si la contraseña es correcta
            if($this->passwordEncoder->isPasswordValid($usuarioDB, $pass)){
              $statusCode = Response::HTTP_OK;
              $respJson = (object) null;
              $respJson->id = $usuarioDB->getId();
              $respJson->nombre = $usuarioDB->getNombre();
              $respJson->apellido = $usuarioDB->getApellido();
              $respJson->correo = $usuarioDB->getCorreo();
              $respJson->usuario = $usuarioDB->getNombreUsuario();
            }
            else{
              $statusCode = Response::HTTP_BAD_REQUEST;
              $respJson->messaging = "La contraseña no es correcta";
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
     * Resetea la contraseña del usuario
     * @Rest\Post("/user/recovery"), defaults={"_format"="json"})
     * 
     * @return Response
     */
    public function recoveryPass(Request $request){

      $respJson = (object) null;
      $statusCode;

      // vemos si existe un body
      if(!empty($request->getContent())){

        $repository=$this->getDoctrine()->getRepository(Usuario::class);
  
        // recuperamos los datos del body y pasamos a un array
        $dataUserRequest = json_decode($request->getContent());
        
          // recuperamos los datos del body
          $usuario = $dataUserRequest->usuario;
            
          // controlamos que el usuario exista
          $repositoryComp = $this->getDoctrine()->getRepository(Usuario::class);
          $usuarioRegistrado = $repository->findOneBy(['nombreUsuario' => $usuario]);

          // si no encontramos un usuario con el nombre de usuario pasamos a probar con el correo
          if($usuarioRegistrado == NULL){
            $statusCode = Response::HTTP_BAD_REQUEST;
            $respJson->messaging = "Usuario inexistente";
          }
          else{
            // TODO: reset pass user (colocamos el nombre de usuario como contraseña)
            // encriptamos la contraseña
            $passHash = $this->passwordEncoder->encodePassword($usuarioRegistrado, $usuarioRegistrado->getNombreUsuario());
            $usuarioRegistrado->setPass($passHash);
    
            $em = $this->getDoctrine()->getManager();
            $em->persist($usuarioRegistrado);
            $em->flush();

            $statusCode = Response::HTTP_OK;
            $respJson->messaging = "La contraseña fue reestablecida. Vuelva a iniciar sesion.";
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