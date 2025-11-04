<?php
namespace iutnc\NetVOD\action;


class DefaultAction extends Action{

    public function execute(): string
    {
//        if(isset($_SESSION['user'])){
//            $query = prepare(Select titre
//                             from StatutVideo inner join
//                            where favori = '1');
//            $query->execute();
//
//        }
        return "Bienvenue";
    }


}
