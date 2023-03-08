<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'bienvenue')]
class BienvenueController extends AbstractController
{

    #[Route('/', name: '')]
    public function bienvenueAction(): Response
    {
        return $this->render('bienvenue/bienvenue.html.twig');
    }
}
