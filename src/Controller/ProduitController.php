<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\DataTransformer\StringToFloatTransformer;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit', name: 'produit')]
class ProduitController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function listAction(EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produitsObj = $produitRepository->findAll();

        $produits = array();
        foreach ($produitsObj as &$produit){
            $produits[$produit->getId()] = array('id'=>$produit->getId(),
                'libelle'=>$produit->getLibelle(),
                'prix'=>$produit->getPrix(),
                'quantite'=>$produit->getQuantite(),
            );

        }
        $args = array('produits' => $produits);

        return $this->render('/produit/list.html.twig', $args);
    }

    #[Route('/ajouter', name: '_ajouter')]
    public function ajouterAction(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $produit->setPrix(1.0);
        $produit->setLibelle('Libelle du produit');
        $produit->setQuantite('0');

        $form = $this->createFormBuilder($produit)
            ->add('libelle', TextType::class)
            ->add('prix', NumberType::class)
            ->add('quantite', IntegerType::class)
            ->add('save', SubmitType::class, ['label' => 'Ajouter le produit'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()){
                $newProduit = $form->getData();

                $em->persist($newProduit);
                $em->flush();
                $this->addFlash('info', 'Le produit a été ajouté');
                return $this->redirectToRoute('produit_list');
            }
            else{
                $this->addFlash('info', 'Le formulaire n\'est pas valide');
            }

        }


        return $this->render('/produit/ajouter.html.twig', ['form'=>$form]);
    }
}
