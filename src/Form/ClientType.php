<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', TextType::class, ['label'=> 'login','attr'=>['placeholder'=> 'login']])
            ->add('password', TextType::class, ['label'=> 'password','attr'=>['placeholder'=> 'password']])
            ->add('nom', TextType::class, ['label'=> 'nom','attr'=>['placeholder'=> 'nom']])
            ->add('prenom', TextType::class, ['label'=> 'prenom','attr'=>['placeholder'=> 'prenom']])
            ->add('dateNaissance', DateType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
