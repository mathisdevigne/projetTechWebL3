<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'bienvenue')]
class BienvenueController extends AbstractController
{

    #[Route('', name: '')]
    public function bienvenueAction(): Response
    {
        return $this->render('vente/bienvenue/bienvenue.html.twig');
    }
}
