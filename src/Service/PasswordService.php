<?php

namespace App\Service;
class PasswordService
{
    public function isPasswordStrongEnough(string $password){
        return !(in_array($password, ['azerty','qwerty','123456']) || strlen($password)<6);
    }
}