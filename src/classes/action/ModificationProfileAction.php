<?php

namespace iutnc\NetVOD\action;
class ModificationProfileAction extends Action
{

    public function execute(): string
    {
        if(isset($_SESSION['user'])){
            $html="";

            return $html;
        }
        return "Vous n'êtes pas connecté";
    }
}