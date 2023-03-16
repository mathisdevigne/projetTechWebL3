<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Panier;
use App\Service\PasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{

    public function menuAction(EntityManagerInterface $em, PasswordService $passwordService, UserPasswordHasherInterface $hasher): Response
    {
        if($this->getUser()){
            $nom = $this->getUser()->getNom(). " ". $this->getUser()->getPrenom();
        }
        else{
            $nom = '';
        }
        $args = array();
        $menu = array();
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            $header = 'https://static.vecteezy.com/ti/vecteur-libre/t2/3586294-abstract-banner-design-web-templates-horizontal-header-web-banner-modern-abstract-cover-header-background-for-website-design-social-media-cover-advertising-banner-flyer-invitation-card-gratuit-vectoriel.jpg';
            $role = 'super administrateur ';
            $menu['Déconnexion'] = 'app_logout';
            $menu['Page d\'accueil'] = 'bienvenue';
            $menu['Ajouter un administrateur'] = 'client_creer_admin';
            $menu['Profil'] = 'client_profil';
        }
        elseif($this->isGranted('ROLE_ADMIN')){
            $header = 'https://static.vecteezy.com/ti/vecteur-libre/t2/3586294-abstract-banner-design-web-templates-horizontal-header-web-banner-modern-abstract-cover-header-background-for-website-design-social-media-cover-advertising-banner-flyer-invitation-card-gratuit-vectoriel.jpg';
            $role = 'administrateur ';
            $menu['Gerer les clients'] = 'client_gerer';
            $menu['Creer un produit'] = 'produit_ajouter';
            $menu['Truman Show'] = 'https://fr.wikipedia.org/wiki/The_Truman_Show';

        }
        elseif($this->isGranted('ROLE_CLIENT')){
            $header = 'https://static.vecteezy.com/ti/vecteur-libre/t2/3586244-abstract-banner-design-web-templates-horizontal-header-web-banner-modern-abstract-cover-header-background-for-website-design-social-media-cover-advertising-banner-flyer-invitation-card-gratuit-vectoriel.jpg';
            $role = 'client ';
            $menu['Déconnexion'] = 'app_logout';
            $menu['Page d\'accueil'] = 'bienvenue';
            $menu['Profil'] = 'client_profil';
            $menu['Nos produits'] = 'produit_list';
            $menu['Panier'] = 'panier';

        }
        else{
            $header = 'https://static.vecteezy.com/ti/vecteur-libre/t2/3586246-abstract-banner-design-web-templates-horizontal-header-web-banner-modern-abstract-cover-header-background-for-website-design-social-media-cover-advertising-banner-flyer-invitation-card-gratuit-vectoriel.jpg';
            $role = '';
            $menu['Connexion'] = 'app_login';
            $menu['Page d\'accueil'] = 'bienvenue';
            $menu['Créer un compte'] = 'client_creer';

        }

        $args['menu'] = $menu;
        $args['nom'] = $nom;
        $args['role']= $role;
        $args['headerUrl']= $header;
        if($this->isGranted('ROLE_CLIENT') and !$this->isGranted('ROLE_SUPER_ADMIN')){
            $paniers = $em->getRepository(Panier::class)->findByClient($this->getUser());
            $quantite = 0;
            foreach($paniers as &$panier){
                $quantite+=$panier->getQuantite();
            }
            $args['nbArticlePanier'] = $quantite;

            $client = new Client($this->getUser());
            $args['passSec'] = false;// $passwordService->isPasswordStrongEnough($client, $hasher); todo fix
        }
        return $this->render('menu.html.twig', $args);
    }
}
