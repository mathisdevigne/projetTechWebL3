<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Function_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\ResolveBindingsPass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier', name: 'panier')]
class PanierController extends AbstractController
{

    public function blockSuperAdmin(): bool{
        if($this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('info', 'Les super administrateurs n\'ont pas de panier');
            return true;
        }
        return false;
    }
    #[Route('/ajouter/{idProd}/{quantite}', name: '_ajouter', requirements: ['idProd' => '0|[1-9]\d*','quantite' => '-?[1-9]\d*'] )]
    public function ajouterAction(int $idProd, int $quantite, EntityManagerInterface $em): Response{
        if($this->blockSuperAdmin()) {
            return $this->redirectToRoute('produit_list');
        }

        $produit = $em->getRepository(Produit::class)->find($idProd);
        if(is_null($produit) || $produit->getQuantite() < $quantite ){
            $this->addFlash('info', 'Pas assez de quantite');
            return $this->redirectToRoute('produit_list');
        }

        $panier = $em->getRepository(Panier::class)->findOneByProduit($produit);
        if(is_null($panier)){
            $panier = new Panier();
        }
        elseif(($panier->getQuantite() + $quantite) <= 0){
            $this->addFlash('info', 'Quantite incoherente');
            return $this->redirectToRoute('panier');
        }
        $produit->setQuantite($produit->getQuantite() - $quantite);
        $panier->setQuantite($panier->getQuantite()+$quantite);
        $panier->setClient($this->getUser());
        $panier->setProduit($produit);

        $em->persist($produit);
        $em->flush();
        $em->persist($panier);
        $em->flush();
        return $this->redirectToRoute('panier');
    }

    #[Route('/supprimer/{idProd}', name: '_supprimer', requirements: ['idProd' => '0|[1-9]\d*'] )]
    public function supprimerAction(int $idProd, EntityManagerInterface $em): Response{
        if($this->blockSuperAdmin()) {
            return $this->redirectToRoute('produit_list');
        }

        $produit = $em->getRepository(Produit::class)->find($idProd);
        if(is_null($produit)){
            $this->addFlash('info', 'Produit inexistant');
            return $this->redirectToRoute('panier');
        }

        $panier = null;
        foreach($em->getRepository(Panier::class)->findByProduit($produit) as &$panierObj){
            if($panierObj->getClient()->getId() == $this->getUser()->getId())
                $panier = $panierObj;
        }
        if(is_null($panier)){
            $this->addFlash('info', 'Pas de panier correspondant');
            return $this->redirectToRoute('panier');
        }

        $produit->setQuantite($produit->getQuantite() + $panier->getQuantite());

        $em->persist($produit);
        $em->remove($panier);
        $em->flush();
        return $this->redirectToRoute('panier');
    }


    #[Route('/vider', name: '_vider', )]
    public function viderAction(EntityManagerInterface $em): Response{
        if($this->blockSuperAdmin()) {
            return $this->redirectToRoute('produit_list');
        }

        $paniers = $em->getRepository(Panier::class)->findByClient($this->getUser());

        foreach($paniers as &$panier){
            $produit = $panier->getProduit();
            $produit->setQuantite($produit->getQuantite() + $panier->getQuantite());

            $em->persist($produit);
            $em->remove($panier);
            $em->flush();
        }
        $this->addFlash('info', 'Panier vidé');
        return $this->redirectToRoute('panier');
    }


    #[Route('/commander', name: '_commander', )]
    public function commanderAction(EntityManagerInterface $em): Response{
        if($this->blockSuperAdmin()) {
            return $this->redirectToRoute('produit_list');
        }

        $paniers = $em->getRepository(Panier::class)->findByClient($this->getUser());

        foreach($paniers as &$panier){
            $em->remove($panier);
            $em->flush();
        }
        $this->addFlash('info', 'Panier commandé');
        return $this->redirectToRoute('panier');
    }

    #[Route('', name: '', )]
    public function panierAction(Request $request, EntityManagerInterface $em): Response
    {
        if($this->blockSuperAdmin()) {
            return $this->redirectToRoute('produit_list');
        }

        $paniers = array();
        foreach($em->getRepository(Panier::class)->findByClient($this->getUser()->getId()) as &$panierObj){
            $paniers[] = array('id'=>$panierObj->getId(),
                'Libelle'=>$panierObj->getProduit()->getLibelle(),
                'Quantite'=>$panierObj->getQuantite(),
                'Prix'=>$panierObj->getProduit()->getPrix()*$panierObj->getQuantite(),
                'idProd'=>$panierObj->getProduit()->getId(),
            );
        }

        return $this->render('/vente/panier/panier.html.twig', array('paniers'=>$paniers));
    }
}
