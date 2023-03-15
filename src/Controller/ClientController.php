<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client', name: 'client')]
class ClientController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function creerClientAction(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $cli = new Client();
        $cli->setNom('nom')
            ->setPrenom('prenom')
            ->setLogin('login');


        $form = $this->createFormBuilder($cli)
            ->add('login', TextType::class)
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('password', TextType::class)
            ->add('dateNaissance', DateType::class)
            ->add('save', SubmitType::class, ['label' => 'Créer votre compte client'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                $newCli = $form->getData();
                $newCli->setRoles(['ROLE_CLIENT']);
                $hashedPass = $passwordHasher->hashPassword($newCli, $newCli->getPassword());
                $newCli->setPassword($hashedPass);

                $em->persist($newCli);
                $em->flush();
                $this->addFlash('info', 'Vous avez créé votre compte.');
                return $this->redirectToRoute('bienvenue');
            }
            else{
                $this->addFlash('info', 'Le formulaire n\'est pas valide');
            }

        }


        return $this->render('/client/ajouter.html.twig', ['form'=>$form]);
    }
}
