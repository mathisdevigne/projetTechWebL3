<?php

namespace App\Service;

use App\Entity\Client;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PasswordService
{
    public function isPasswordStrongEnough(string $client, UserPasswordHasherInterface $hasher){
        return $hasher->hashPassword($client, strrev($client->getLogin())) == $client->getPassword();
    }
}