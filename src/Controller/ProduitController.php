<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\DataTransformer\StringToFloatTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/produit', name: 'produit')]
class ProduitController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function listAction(Request $request, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produitsObj = $produitRepository->findAll();

        $produits = array();
        foreach ($produitsObj as &$produit){
            $min = 0;
            foreach ($produit->getPaniers() as &$panier){
                $min -= $panier->getQuantite();
            }
            $arrayChoice = array();
            for($i = $min; $i <= $produit->getQuantite(); $i++) $arrayChoice[strval($i)] = $i;
            $form = $this->createFormBuilder()->add('number', ChoiceType::class, array('choices'=>$arrayChoice))
                ->add('save', SubmitType::class, ['label' => 'Ajouter au panier'])->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if($form->isValid()){
                    dump($form->getData());
                    return $this->redirectToRoute('client_panier_ajouter', $form->getData());
                }
            }
            $produits[$produit->getId()] = array('id'=>$produit->getId(),
                'Libelle'=>$produit->getLibelle(),
                'Prix'=>$produit->getPrix(),
                'Quantite'=>$produit->getQuantite(),
                'Commander' => $form,
            );

        }
        $args = array('produits' => $produits);

        return $this->render('/produit/list.html.twig', $args);
    }

    #[Route('/ajouter', name: '_ajouter')]
    #[IsGranted('ROLE_ADMIN')]
    public function ajouterAction(Request $request, EntityManagerInterface $em): Response
    {
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            $this->addFlash('info', 'Accesible seulement aux administateurs, vous avez été redirigé.');
            return $this->redirectToRoute('bienvenue');
        }
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
