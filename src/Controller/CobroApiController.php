<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\Cobro;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;
use App\Entity\Token;
use App\Entity\User;

#[Route('/api/cobro')]
class CobroApiController extends AbstractController
{
    /**
     * TOKEN
     * CREAR TOKEN SI NO VALID
     * COMPROBAR TOKEN
     */

    // Per a el controlador CobroApi necessitarem (sempre es comprovarà el token i l'usuari).
    /**
     * 1.- Crear cobro  X
     * 2.- Mostrar cobros actius on creador sòc jo (es pot passar limit)   X
     * 3.- Mostrar cobros actius on receptor sòc jo (es pot passar limit)  X
     * 4.- Mostrar històric de cobros (completats) on creador sòc jo   X
     * 5.- Mostrar històric de cobros (completats) on receptor sòc jo  X
     * 6.- Mostrar cobros almacentats ("eliminitat") on el creador sòc jo.  X
     * 7.- Almacenar cobrament    X
     * 8.- Passar a revisat (pendent de revisar sols els que estic jo com a receptor)   X
     * 9.- Completar cobrament.   X
     */

    // 0.- CREAR COBRO
    #[Rest\Put('/crear', name: 'cobro_api_new')]
    public function crearCobroApi(
        ManagerRegistry $doctrine, EntityManagerInterface $entityManager,
        Request $request, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos = json_decode($content, true);
            // $token = $datos["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
            $datos_cobro = $datos["cobro"];

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);
                $usuarioReceptor = $doctrine->getRepository(User::class)->find($datos_cobro["receptor"]);

                $cobro = new Cobro();
                $cobro->setCreador($datos_token[0]->getIdUsuario()); // el creador es el mismo que hace la petición
                $cobro->setReceptor($usuarioReceptor);
                $cobro->setCreacion(DateTime::createFromFormat('Y-m-d H:i:s', $datos_cobro['creacion']));
                $cobro->setTitulo($datos_cobro["titulo"]);
                $cobro->setTexto($datos_cobro["texto"]);
                $cobro->setRevisado(false);
                $cobro->setCompletado(false);
                $cobro->setFechaCompletado(null);
                $cobro->setArchivado(false);

                $error = $validator->validate($cobro);
                if (count($error) == 0) {
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($cobro);
                    $entityManager->flush();

                    $response = [
                        'ok' => true,
                        'msg' => "Cobro creado correctamente"
                    ];
                } else {
                    $response = [
                        'ok' => false,
                        'err' => 2002,
                        'msg' => "Error al crear el cobro"
                    ];
                }
            } else {
                // TOKEN NO VALIDO (EL FRONT PEDIRÁ LOGIN DE NUEVO)
                $response = [
                    'ok' => false,
                    'err' => 2001,
                    'error' => 'Token expirado'
                ];
            }
        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 2000,
                'error' => 'Error intentar crear el cobro: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/activos-enviados', name: 'cobro_api_activos_enviados')]
    public function activosEnviados(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $datos = json_decode($content, true);
        // $token = $datos["token"];
        $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
        $limite = $datos["limite"];
        if ($limite !== "")
            $limite = intval($limite);

        if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
            $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

            $cobros = $doctrine->getRepository(Cobro::class)->getEnviados($datos_token[0]->getIdUsuario(), $limite);
            $cobrosList = [];

            foreach ($cobros as $cobro) {
                $cobrosList[] = $cobro->toArray();
            }

            $response = [
                'ok' => true,
                'cobros' => $cobrosList,
            ];
        } else {
            $response = [
                'ok' => false,
                'err' => 2010,
                'msg' => "Token expirado"
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/activos-recibidos', name: 'cobro_api_activos_recibidos')]
    public function activosRecibidos(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $datos = json_decode($content, true);
        // $token = $datos["token"];
        $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
        $limite = $datos["limite"];
        if ($limite !== "")
            $limite = intval($limite);

        if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
            $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

            $cobros = $doctrine->getRepository(Cobro::class)->getRecibidos($datos_token[0]->getIdUsuario(), $limite);
            $cobrosList = [];

            foreach ($cobros as $cobro) {
                $cobrosList[] = $cobro->toArray();
            }

            $response = [
                'ok' => true,
                'cobros' => $cobrosList,
            ];
        } else {
            $response = [
                'ok' => false,
                'err' => 2010,
                'msg' => "Token expirado"
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/historico-enviados', name: 'cobro_api_historico_enviados')]
    public function historicoEnviados(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $datos = json_decode($content, true);
        // $token = $datos["token"];
        $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
        $limite = $datos["limite"];
        if ($limite !== "")
            $limite = intval($limite);

        if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
            $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

            $cobros = $doctrine->getRepository(Cobro::class)->getHistoricoEnviados($datos_token[0]->getIdUsuario(), $limite);
            $cobrosList = [];

            foreach ($cobros as $cobro) {
                $cobrosList[] = $cobro->toArray();
            }

            $response = [
                'ok' => true,
                'cobros' => $cobrosList,
            ];
        } else {
            $response = [
                'ok' => false,
                'err' => 2010,
                'msg' => "Token expirado"
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/historico-recibidos', name: 'cobro_api_historico_recibidos')]
    public function historicoRecibidos(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $datos = json_decode($content, true);
        // $token = $datos["token"];
        $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
        $limite = $datos["limite"];
        if ($limite !== "")
            $limite = intval($limite);

        if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
            $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

            $cobros = $doctrine->getRepository(Cobro::class)->getHistoricoRecibidos($datos_token[0]->getIdUsuario(), $limite);
            $cobrosList = [];

            foreach ($cobros as $cobro) {
                $cobrosList[] = $cobro->toArray();
            }

            $response = [
                'ok' => true,
                'cobros' => $cobrosList,
            ];
        } else {
            $response = [
                'ok' => false,
                'err' => 2010,
                'msg' => "Token expirado"
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Post('/almacenados', name: 'cobro_api_almacenados')]
    public function getAlmacenados(ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $datos = json_decode($content, true);
        // $token = $datos["token"];
        $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
        $limite = $datos["limite"];
        if ($limite !== "")
            $limite = intval($limite);

        if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
            $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

            $cobros = $doctrine->getRepository(Cobro::class)->getArchivados($datos_token[0]->getIdUsuario(), $limite);
            $cobrosList = [];

            foreach ($cobros as $cobro) {
                $cobrosList[] = $cobro->toArray();
            }

            $response = [
                'ok' => true,
                'cobros' => $cobrosList,
            ];
        } else {
            $response = [
                'ok' => false,
                'err' => 2010,
                'msg' => "Token expirado"
            ];
        }
        return new JsonResponse($response);
    }

    #[Rest\Put('/revisar', name: 'cobro_api_revisar')]
    public function setActivarRevisar(
        ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos = json_decode($content, true);
            // $token = $datos["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
            $id_cobro = $datos["cobro"];

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

                $cobro = $doctrine->getRepository(Cobro::class)->find($id_cobro);
                if (!$cobro) {
                    $response = [
                        'ok' => false,
                        'err' => 2030,
                        'error' => 'Cobro no encontrado',
                    ];
                } else {
                    if ($cobro->getReceptor()->getId() !== $datos_token[0]->getIdUsuario()->getId()) {
                        $response = [
                            'ok' => false,
                            'err' => 2031,
                            'error' => 'El cobro no pertenece a este usuario',
                        ];
                    } else {

                        if ($cobro->isArchivado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "El cobro ya está archivado"
                            ]);
                        }

                        if ($cobro->isCompletado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "El cobro ya está completado"
                            ]);
                        }

                        if ($cobro->isRevisado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "Cobro ya esta pendiente de revisar"
                            ]);
                        }

                        $cobro->setRevisado(true);
                        $error = $validator->validate($cobro);
                        if (count($error) == 0) {
                            $entityManager = $doctrine->getManager();
                            $entityManager->flush();

                            $response = [
                                'ok' => true,
                                'msg' => "Cobro pasado a pendiente de revisión correctamente"
                            ];
                        } else {
                            $response = [
                                'ok' => false,
                                'err' => 2032,
                                'msg' => "Error al intentar pasar a pendiente el cobro"
                            ];
                        }

                    }
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

    #[Rest\Put('/completar', name: 'cobro_api_completar')]
    public function setCompletar(
        ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos = json_decode($content, true);
            // $token = $datos["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
            $id_cobro = $datos["cobro"];

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

                $cobro = $doctrine->getRepository(Cobro::class)->find($id_cobro);
                if (!$cobro) {
                    $response = [
                        'ok' => false,
                        'err' => 2041,
                        'error' => 'Cobro no encontrado',
                    ];
                } else {
                    if ($cobro->getCreador()->getId() !== $datos_token[0]->getIdUsuario()->getId()) {
                        $response = [
                            'ok' => false,
                            'err' => 2042,
                            'error' => 'El cobro no pertenece a este usuario',
                        ];
                    } else {

                        if ($cobro->isArchivado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "El cobro ya está archivado"
                            ]);
                        }

                        if ($cobro->isCompletado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "El cobro ya está completado"
                            ]);
                        }

                        $cobro->setCompletado(true);
                        $cobro->setFechaCompletado(new DateTime());
                        $error = $validator->validate($cobro);
                        if (count($error) == 0) {
                            $entityManager = $doctrine->getManager();
                            $entityManager->flush();

                            $response = [
                                'ok' => true,
                                'msg' => "Cobro pasado a completado correctamente"
                            ];
                        } else {
                            $response = [
                                'ok' => false,
                                'err' => 2043,
                                'msg' => "Error al intentar pasar a completado el cobro"
                            ];
                        }
                    }
                }
            } else {
                // TOKEN NO VALIDO (EL FRONT PEDIRÁ LOGIN DE NUEVO)
                $response = [
                    'ok' => false,
                    'err' => 2040,
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

    #[Rest\Put('/archivar', name: 'cobro_api_archivar')]
    public function setArchivar(
        ManagerRegistry $doctrine, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos = json_decode($content, true);
            // $token = $datos["token"];
            $token = str_replace("Bearer ", "", $request->headers->get("authorization"));
            $id_cobro = $datos["cobro"];

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);

                $cobro = $doctrine->getRepository(Cobro::class)->find($id_cobro);
                if (!$cobro) {
                    $response = [
                        'ok' => false,
                        'err' => 2051,
                        'error' => 'Cobro no encontrado',
                    ];
                } else {
                    if ($cobro->getCreador()->getId() !== $datos_token[0]->getIdUsuario()->getId()) {
                        $response = [
                            'ok' => false,
                            'err' => 2052,
                            'error' => 'El cobro no pertenece a este usuario',
                        ];
                    } else {

                        if ($cobro->isArchivado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "El cobro ya está archivado"
                            ]);
                        }

                        if ($cobro->isCompletado()) {
                            return new JsonResponse([
                                "ok" => false,
                                "msg" => "El cobro ya está completado"
                            ]);
                        }

                        $cobro->setArchivado(true);
                        $error = $validator->validate($cobro);
                        if (count($error) == 0) {
                            $entityManager = $doctrine->getManager();
                            $entityManager->flush();

                            $response = [
                                'ok' => true,
                                'msg' => "Cobro archivado correctamente"
                            ];
                        } else {
                            $response = [
                                'ok' => false,
                                'err' => 2053,
                                'msg' => "Error al intentar almacenar el cobro"
                            ];
                        }
                    }
                }
            } else {
                // TOKEN NO VALIDO (EL FRONT PEDIRÁ LOGIN DE NUEVO)
                $response = [
                    'ok' => false,
                    'err' => 2040,
                    'error' => 'Token expirado'
                ];
            }
        } catch (\Throwable $e) {
            $response = [
                'ok' => false,
                'err' => 2050,
                'error' => 'Error al recuperar datos personales: ' . $e->getMessage(),
            ];
        }
        return new JsonResponse($response);
    }
}