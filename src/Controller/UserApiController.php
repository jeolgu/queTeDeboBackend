<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\DatosPersonales;

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
     * 1.- Comprobar usuari i pass (LOGIN)
     * 2.- Mostrar dades personals (Necessari comprobar token i usuari)
     * 3.- Modificar dades personals (Necessari comprobar token i usuari).
     */

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