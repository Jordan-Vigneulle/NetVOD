<?php

declare(strict_types = 1);
namespace iutnc\NetVOD\exception;

class AuthError extends \Exception{


    public function __construct(String $mess){
        parent::__construct($mess);
    }

}