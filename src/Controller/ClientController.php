<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Produit;
use App\Form\ClientType;
use App\Service\PasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client', name: 'client')]
class ClientController extends AbstractController
{
    #[Route('/creer', name: '_creer')]
    public function creerClientAction(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, PasswordService $passwordService): Response
    {
        $cli = new Client();
        $cli->setNom('nom')
            ->setPrenom('prenom')
            ->setLogin('login');


        $form = $this->createForm(ClientType::class);
        $form->add('save', SubmitType::class, ['label' => 'Créer votre compte client']);


        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                $newCli = $form->getData();
                $newCli->setRoles(['ROLE_CLIENT']);
                if(!$em->getRepository(Client::class)->findOneByLogin($newCli->getLogin())){
                    if($passwordService->isPasswordStrongEnough($newCli->getPassword())){
                        $hashedPass = $passwordHasher->hashPassword($newCli, $newCli->getPassword());
                        $newCli->setPassword($hashedPass);
                        $em->persist($newCli);
                        $em->flush();
                        $this->addFlash('info', 'Vous avez créé votre compte.');
                        return $this->redirectToRoute('bienvenue');
                    }
                    else{
                        $this->addFlash('info', 'Le mots de passe n\'est pas assez sécurisé');
                    }
                }
                else{
                    $this->addFlash('info', 'Login \'' . $newCli->getLogin() .'\' existant.');
                }

            }
            else{
                $this->addFlash('info', 'Le formulaire n\'est pas valide');
            }

        }


        return $this->render('/vente/client/ajouter.html.twig', ['form'=>$form]);
    }
    #[Route('/gerer', name: '_gerer')]
    #[IsGranted('ROLE_ADMIN')]
    public function gererClientAction(Request $request, EntityManagerInterface $em): Response
    {
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            $this->addFlash('info', 'Accesible seulement aux administateurs, vous avez été redirigé.');
            return $this->redirectToRoute('bienvenue');
        }
        $clientRep = $em->getRepository(Client::class);
        $clientsObj = $clientRep->findAll();

        $clients = array();
        foreach ($clientsObj as &$client){
            $clients[$client->getId()] = array('id'=>$client->getId(),
                'login'=>$client->getLogin(),
                'password'=>$client->getPassword(),
                'prenom'=>$client->getPrenom(),
                'nom'=>$client->getNom(),
                'date'=>$client->getDateNaissance()->format('d/m/Y'),
                'roles'=>$client->getRoles(),
            );

        }
        return $this->render('/vente/client/gerer.html.twig', ['clients'=>$clients]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/gerer/supprimer/{id}', name: '_supprimer', requirements: ['id' => '0|[1-9]\d*'],)]
    public function supprClientAction(int $id, Request $request, EntityManagerInterface $em): Response
    {
        if($this->isGranted('ROLE_SUPER_ADMIN')){
            $this->addFlash('info', 'Accesible seulement aux administateurs, vous avez été redirigé.');
            return $this->redirectToRoute('bienvenue');

        }
        $clientRep = $em->getRepository(Client::class);
        $client = $clientRep->find($id);

        if($this->getUser() != $client and !in_array('ROLE_SUPER_ADMIN', $client->getRoles())){
            $this->addFlash('info', 'Utilisateur n°' . $client->getId() . ' ' . $client->getLogin() . ' supprimé.');
            $em->remove($client);
            $em->flush();
        }
        else{
            $this->addFlash('info', 'Pas possible de supprimer cet utilisateur.');
        }
        return $this->redirectToRoute('client_gerer');
    }
    #[Route('/gerer/creer-admin', name: '_creer_admin', )]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function creerAdminAction(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, PasswordService $passwordService): Response
    {
        $cli = new Client();
        $cli->setNom('nom')
            ->setPrenom('prenom')
            ->setLogin('login');


        $form = $this->createForm(ClientType::class);
        $form->add('save', SubmitType::class, ['label' => 'Créer un administrateur']);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                $newCli = $form->getData();
                if(!$em->getRepository(Client::class)->findOneByLogin($newCli->getLogin())){
                    if($passwordService->isPasswordStrongEnough($newCli->getPassword())){
                        $newCli->setRoles(['ROLE_ADMIN']);
                        $hashedPass = $passwordHasher->hashPassword($newCli, $newCli->getPassword());
                        $newCli->setPassword($hashedPass);

                        $em->persist($newCli);
                        $em->flush();
                        $this->addFlash('info', 'Vous avez créé l\'administrateur ' . $newCli->getLogin() .'.');
                    }
                    else{
                        $this->addFlash('info', 'Le mots de passe n\'est pas assez sécurisé');
                    }
                }
                else{
                    $this->addFlash('info', 'Login existant ' . $newCli->getLogin() .'.');
                }

            }
            else{
                $this->addFlash('info', 'Le formulaire n\'est pas valide');
            }
        }


        return $this->render('/vente/client/ajouter-admin.html.twig', ['form'=>$form]);
    }
    #[Route('/profil', name: '_profil', )]
    public function profilAction(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ClientType::class);
        $form->add('save', SubmitType::class, ['label' => 'Modifier votre compte client']);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if($form->isValid()){
                $newCli = $form->getData();

                $hashedPass = $passwordHasher->hashPassword($newCli, $newCli->getPassword());
                $newCli->setPassword($hashedPass);

                $em->persist($newCli);
                $em->flush();
                $this->addFlash('info', 'Vous avez modifié votre profil.');

                if($this->isGranted('ROLE_SUPER_ADMIN')){
                    return $this->redirectToRoute('bienvenue');
                }
                else{
                    return $this->redirectToRoute('produit_list');
                }
            }
            else{
                $this->addFlash('info', 'Le formulaire n\'est pas valide');
            }

        }

        return $this->render('/vente/client/profil.html.twig', ['form'=>$form]);
    }

}
