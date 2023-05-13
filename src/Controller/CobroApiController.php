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
     * 1.- Crear cobro 
     * 2.- Mostrar cobros actius on creador sòc jo (es pot passar limit)
     * 3.- Mostrar cobros actius on receptor sòc jo (es pot passar limit)
     * 4.- Mostrar històric de cobros (completats) on creador sòc jo
     * 5.- Mostrar històric de cobros (completats) on receptor sòc jo
     * 6.- Mostrar cobros almacentats ("eliminitat") on el creador sòc jo.
     */

     // 0.- CREAR USUARI
    // NEW BOOK
    #[Rest\Put('/crear', name: 'cobro_api_new')]
    public function crearCobroApi(
        ManagerRegistry $doctrine, EntityManagerInterface $entityManager,
         Request $request, ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Comprobar que el token es valido y aún no ha expirado
            $content = $request->getContent();
            $datos = json_decode($content, true);
            $token = $datos["token"];
            $datos_cobro = $datos["cobro"];

            if ($doctrine->getRepository(Token::class)->compruebaToken($token)) {
                // MUESTRO DATOS
                $datos_token = $doctrine->getRepository(Token::class)->dameUsuarioPorToken($token);
                $usuarioReceptor = $doctrine->getRepository(User::class)->find($datos_cobro["receptor"]);
               
                $cobro = new Cobro();
                $cobro->setCreador($datos_token[0]->getIdUsuario()); // el creador es el mismo que hace la petición
                $cobro->setReceptor($usuarioReceptor);
                $cobro->setCreacion( DateTime::createFromFormat('Y-m-d H:i:s', $datos_cobro['creacion']));
                $cobro->setTitulo( $datos_cobro["titulo"] );
                $cobro->setTexto( $datos_cobro["texto"]);
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
}