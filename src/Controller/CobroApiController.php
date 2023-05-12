<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\Cobro;

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

}