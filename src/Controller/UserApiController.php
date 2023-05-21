<?php

namespace App\Controller;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\DatosPersonales;
use App\Entity\Token;

#[Route('/api/user')]
class UserApiController extends AbstractController
{
    /**
     * TOKEN
     * CREAR TOKEN SI NO VALID
     * COMPROBAR TOKEN
     */

    // Per a el controlador UserApi necessitarem.
    /**
     * 0.- Crear usuari (sense comprobar token encara no esta creat). X // posibilidad de enviar mail.
     * 1.- Comprobar usuari i pass (LOGIN) x
     * 2.- Mostrar dades personals (Necessari comprobar token) X
     * 3.- Modificar dades personals (Necessari comprobar token). X
     * 4.- Modificar password X
     */

    // 0.- CREAR USUARI
    #[Rest\Put('/crear', name: 'usuario_api_new')]
    public function crearUsuarioApi(
        ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager, Request $request, ValidatorInterface $validator
    ): JsonResponse {
        try {

            $content = $request->getContent();
            $email = json_decode($content, true)["usuario"];
            $password = json_decode($content, true)["password"];
            $busqueda = $doctrine->getRepository(User::class)->findByEmail($email);

            if (sizeof($busqueda) == 0) {
                // NO EXISTE POR LO TANTO SE CREA
                $usuario = new User();
                $usuario->setEmail($email);
                $usuario->setPassword(
                    $userPasswordHasher->hashPassword(
                        $usuario,
                        $password
                    )
                );

                $usuario->setRoles(['ROLE_USER']);
                $errorUsuario = $validator->validate($usuario);

                if (count($errorUsuario) == 0) {

                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($usuario);
                    $entityManager->flush();
                    // Con el usuario creado, podremos añadir en la tabla de token y datos personales valores iniciales.

                    //Crear el token valido 1 mes
                    $token_valor = md5(date("Y-m-d H:i:s", strtotime("+1 month", strtotime(date("Y-m-d H:i:s")))));
                    $token = new Token();
                    $token->setIdUsuario($usuario);
                    $token->setToken($token_valor);
                    $token->setExpiracion(new \DateTime('+1 month'));

                    $erroresToken = $validator->validate($token);
                    if (count($erroresToken) == 0) {
                        $entityManager = $doctrine->getManager();
                        $entityManager->persist($token);
                        $entityManager->flush();

                        // Inicializamos los datos personales.
                        $datos_personales = new DatosPersonales();
                        $datos_personales->setIdUsuario($usuario);
                        $datos_personales->setIdiomaPredefinido("ES");
                        $erroresDatosPersonales = $validator->validate($datos_personales);

                        if (count($erroresDatosPersonales) == 0) {
                            $entityManager = $doctrine->getManager();
                            $entityManager->persist($datos_personales);
                            $entityManager->flush();

                            $response = [
                                'ok' => true,
                                'message' => 'Usuario creado con éxito',
                                'token' => $token_valor
                            ];
                        } else {
                            $response = [
                                'ok' => false,
                                'err' => 1004,
                                'message' => 'No se ha podido inicializar los datos personales.'
                            ];
                        }

                    } else {
                        $response = [
                            'ok' => false,
                            'err' => 1003,
                            'message' => 'Usuario creado pero no se ha creado el token y los datos personales.'
                        ];
                    }
                } else {
                    // ERROR EN EL USUARIO
                    $response = [
                        'ok' => false,
                        'err' => 1002,
                        'message' => 'El usuario al crear usuario en el sistema',
                    ];
                }
            } else {
                // DEVOLVEMOS ERROR DE USUARIO YA EXISTE
                $response = [
                    'ok' => false,
                    'err' => 1001,
                    'message' => 'El usuario ya existe en el sistema',
                ];
            }
        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 1000,
                'error' => 'Error al insertar el usuario: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/login', name: 'usuario_api_login')]
    public function login(
        ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher,
        Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator
    ): JsonResponse {

        try {
            $content = $request->getContent();
            $email = json_decode($content, true)["usuario"];
            $password = json_decode($content, true)["password"];
            $login = $doctrine->getRepository(User::class)->findByEmail($email);

            if (sizeof($login) == 0 || sizeof($login) > 1) {
                $response = [
                    'ok' => false,
                    'err' => 1011,
                    'message' => 'El usuario no está registrado'
                ];
            } else {
                // AHORA REVISAMOS SI LAS PASS COINCIDEN
                if ($userPasswordHasher->isPasswordValid($login[0], $password)) {
                    // SI TODO VA OK DEVOLVEMOS EL TOKEN CON EL QUE REALIZAREMOS DESPUÉS TODAS LAS ACCIONES
                    $token = $doctrine->getRepository(Token::class)->dameTokenPorIdUsuario($login[0]->getId());
                    $token = (count($token) > 0) ? $token[0]->getToken() : "";

                    if ($token == "") {
                        // SE DEBE REGISTRAR UNO NUEVO
                        $token_valor = md5(date("Y-m-d H:i:s", strtotime("+1 month", strtotime(date("Y-m-d H:i:s")))));
                        $tokenNuevo = new Token();
                        $tokenNuevo->setIdUsuario($login[0]);
                        $tokenNuevo->setToken($token_valor);
                        $tokenNuevo->setExpiracion(new \DateTime('+1 month'));
                        $erroresToken = $validator->validate($tokenNuevo);
                        if (count($erroresToken) == 0) {
                            $entityManager = $doctrine->getManager();
                            $entityManager->persist($tokenNuevo);
                            $entityManager->flush();
                            $token = $tokenNuevo->getToken();
                        }
                    }

                    $response = [
                        'ok' => true,
                        'message' => 'Login correcto',
                        'token' => $token
                    ];
                } else {
                    $response = [
                        'ok' => false,
                        'err' => 1012,
                        'message' => 'Contraseña incorrecta'
                    ];
                }
            }

        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 1010,
                'error' => 'Error al realizar el login: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/datos-personales', name: 'usuario_api_datos')]
    public function getDatosPersonales(
        ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher,
        Request $request, EntityManagerInterface $entityManager
    ): JsonResponse {

        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            // $token = json_decode($content, true)["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);
                $datos_personales = $datos_token[0]->getIdUsuario()->getDatosPersonales();
                $arrDatosPersonales = $datos_personales->toArray();
                $response = [
                    'ok' => true,
                    $arrDatosPersonales
                ];
            } else {
                // TOKEN NO VALIDO (EL FRONT PEDIRÁ LOGIN DE NUEVO)
                $response = [
                    'ok' => false,
                    'err' => 1021,
                    'error' => 'Token expirado'
                ];
            }

        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 1020,
                'error' => 'Error al recuperar datos personales: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);

    }

    #[Rest\Put('/datos-personales', name: 'usuario_api_datos_update')]
    public function setDatosPersonales(
        ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher,
        Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos_personales = json_decode($content, true);

            // $token = $datos["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

                $datosPersonales = $doctrine->getRepository(DatosPersonales::class)->find($datos_token[0]->getIdUsuario());
                //$datosPersonales->fromJson($datos_personales);
                $datosPersonales->setNombre($datos_personales["nombre"]);
                $datosPersonales->setApellidos($datos_personales["apellidos"]);
                $datosPersonales->setEdad($datos_personales["edad"]);
                $datosPersonales->setLocalidad($datos_personales["localidad"]);
                $datosPersonales->setCp($datos_personales["cp"]);
                $datosPersonales->setDireccion($datos_personales["direccion"]);
                $datosPersonales->setPais($datos_personales["pais"]);
                $datosPersonales->setIdiomaPredefinido($datos_personales["idioma_predefinido"]);
                
                $error = $validator->validate($datosPersonales);
                if (count($error) == 0) {
                    $entityManager = $doctrine->getManager();
                    $entityManager->flush();

                    $response = [
                        'ok' => true,
                        'message' => "Datos personales guardados correctamente"
                    ];
                } else {
                    $response = [
                        'ok' => false,
                        'err' => 1032,
                        'message' => "Error al guardar los datos personales"
                    ];
                }
            } else {
                // TOKEN NO VALIDO (EL FRONT PEDIRÁ LOGIN DE NUEVO)
                $response = [
                    'ok' => false,
                    'err' => 1031,
                    'error' => 'Token expirado'
                ];
            }

        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 1030,
                'error' => 'Error al recuperar datos personales: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Put('/mod-pass', name: 'usuario_api_pass_update')]
    public function modPassword(
        ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher,
        Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos = json_decode($content, true);
            // $token = $datos["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
            $passOld = $datos["passO"];
            $passNew = $datos["passN"];

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

                // AL SER EL TOKEN VALIDO COMPROBAMOS QUE LA PASS QUE HAY EN LA BBDD ES LA MISMA PARA ASI AÑADIR SEGURIDAD
                if ($userPasswordHasher->isPasswordValid($datos_token[0]->getIdUsuario(), $passOld)){

                    $datos_token[0]->getIdUsuario()->setPassword(
                        $userPasswordHasher->hashPassword(
                            $datos_token[0]->getIdUsuario(),
                            $passNew
                        )
                    );

                    $entityManager = $doctrine->getManager();
                    $entityManager->flush();

                    $response = [
                        'ok' => true,
                        'message' => 'Contraseña modificada correctamente'
                    ];
                }
                else{
                    $response = [
                        'ok' => false,
                        'err' => 1042,
                        'message' => "Contraseña actual no coincide con la enviada"
                    ];
                }
            } else {
                // TOKEN NO VALIDO (EL FRONT PEDIRÁ LOGIN DE NUEVO)
                $response = [
                    'ok' => false,
                    'err' => 1041,
                    'error' => 'Token expirado'
                ];
            }
        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 1040,
                'error' => 'Error al recuperar datos personales: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);
    }


// // UPDATE
// #[Rest\Put('/guardar-datos-personales', name: 'modificar_datos_personales_api')]
// public function editBook(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator, $isbn = ''): JsonResponse
// {
//     try {
//         $content = $request->getContent();
//         $id_publisher = json_decode($content, true)["publisher"];
//         $publisher = $doctrine->getRepository(Publisher::class)->find($id_publisher);

//         $book = $doctrine->getRepository(Book::class)->find($isbn);

//         $book->fromJson($content, $publisher);
//         $errors = $validator->validate($book);

//         if (count($errors) == 0) {
//             $entityManager = $doctrine->getManager();
//             $entityManager->flush();

//             $response = [
//                 'ok' => true,
//                 'message' => 'book updated',
//             ];
//         } else {

//             $response = [
//                 'ok' => false,
//                 'message' => 'Failed to insert book: errors in data',
//             ];
//         }
//     } catch (\Throwable $e) {
//         $response = [
//             'ok' => false,
//             'error' => 'Failed to update book: ' . $e->getMessage(),
//         ];
//     }

//     return new JsonResponse($response);
// }

// #[Rest\Get('/', name: 'contact_api_list')]
// public function contactApiList(ManagerRegistry $doctrine): JsonResponse
// {
//     $contacts = $doctrine->getRepository(Contact::class)->findAll();
//     $contactsList = [];

//     if (count($contacts) > 0) {
//         foreach ($contacts as $contact) {
//             $contactsList[] = $contact->toArray();
//         }
//         $response = [
//             'ok' => true,
//             'contacts' => $contactsList,
//         ];
//     } else {
//         $response = [
//             'ok' => false,
//             'error' => 'No contacts found',
//         ];
//     }

//     return new JsonResponse($response);
// }
}