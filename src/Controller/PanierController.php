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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier', name: 'panier')]
class PanierController extends AbstractController
{
    #[Route('/ajouter/{idProd}/{quantite}', name: '_ajouter', requirements: ['idProd' => '0|[1-9]\d*','quantite' => '-?\d*'] )]
    #[IsGranted('ROLE_CLIENT')]
    public function ajouterAction(int $idProd, int $quantite, EntityManagerInterface $em): Response{
        if($quantite == 0){
            $this->addFlash('info', 'Selectionnez une quantité');
            return $this->redirectToRoute('produit_list');
        }
        $produit = $em->getRepository(Produit::class)->find($idProd);
        if(is_null($produit) || $produit->getQuantite() < $quantite ){
            $this->addFlash('info', 'Pas assez de quantite');
            return $this->redirectToRoute('produit_list');
        }

        $paniers = $em->getRepository(Panier::class)->findByProduit($produit);
        $panier = null;
        foreach($paniers as &$panierObj){
            if($panierObj->getClient()->getId() == $this->getUser()->getId())
                $panier = $panierObj;
        }
        if(is_null($panier)){
            $panier = new Panier();
        }
        elseif(($panier->getQuantite() + $quantite) < 0){
            $this->addFlash('info', 'Quantite incoherente');
            return $this->redirectToRoute('panier');
        }
        elseif(($panier->getQuantite() + $quantite) == 0){
            return $this->redirectToRoute('panier_supprimer', array('idProd'=>$idProd));
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
    #[IsGranted('ROLE_CLIENT')]
    public function supprimerAction(int $idProd, EntityManagerInterface $em): Response{

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
    #[IsGranted('ROLE_CLIENT')]
    public function viderAction(EntityManagerInterface $em): Response{
        $paniers = $em->getRepository(Panier::class)->findByClient($this->getUser());

        foreach($paniers as &$panier){
            if($panier->getClient()->getId() == $this->getUser()->getId()){
                $produit = $panier->getProduit();
                $produit->setQuantite($produit->getQuantite() + $panier->getQuantite());

                $em->persist($produit);
                $em->remove($panier);
                $em->flush();
            }
        }
        $this->addFlash('info', 'Panier vidé');
        return $this->redirectToRoute('panier');
    }


    #[Route('/commander', name: '_commander', )]
    #[IsGranted('ROLE_CLIENT')]
    public function commanderAction(EntityManagerInterface $em): Response{
        $paniers = $em->getRepository(Panier::class)->findByClient($this->getUser());

        foreach($paniers as &$panier){
            if($panier->getClient()->getId() == $this->getUser()->getId()) {
                $em->remove($panier);
                $em->flush();
            }
        }
        $this->addFlash('info', 'Panier commandé');
        return $this->redirectToRoute('panier');
    }

    #[Route('', name: '', )]
    #[IsGranted('ROLE_CLIENT')]
    public function panierAction(Request $request, EntityManagerInterface $em): Response
    {
        $args = array();
        $paniers = array();
        foreach($em->getRepository(Panier::class)->findByClient($this->getUser()->getId()) as &$panierObj){
            if(!array_key_exists('total', $args)) {$args['total'] = 0;}
            $panier = array('id'=>$panierObj->getId(),
                'Libelle'=>$panierObj->getProduit()->getLibelle(),
                'Quantite'=>$panierObj->getQuantite(),
                'Prix'=>$panierObj->getProduit()->getPrix()*$panierObj->getQuantite(),
                'idProd'=>$panierObj->getProduit()->getId(),
            );
            $paniers[] = $panier;
            $args['total'] += $panier['Prix'];
        }
        if(!empty($paniers)){
            $args['paniers'] = $paniers;
        }
        return $this->render('/vente/panier/panier.html.twig', $args);
    }
}
