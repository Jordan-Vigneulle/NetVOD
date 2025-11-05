<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\repository\NetVODRepository;

require_once 'vendor/autoload.php';

/*
 * Classe permettant de retirer une série des favoris
 *
 * */
class RetirerFavoriAction extends Action
{
    public function execute(): string
    {

        if(!isset($_SESSION['user'])){
            return "<div class='message-info'>Vous devez vous connecter</div>";
        }
        $idSerie = filter_var($_GET['series_id'], FILTER_VALIDATE_INT);
        $r = NetVODRepository::getInstance();
        $verifSerie = $r->getTitre($idSerie);
        if($verifSerie === null){
             return "Echec, cette série n'éxiste pas";
        }
        $resultat = $r->setSerieNonFavoris($idSerie,$_SESSION['user']);
        if($resultat === false){
            $_SESSION['message'] = "Série ajoutée aux favoris !";
            header("Location: ?action=display-catalogue");
            exit();
        }
        header("Location: ?action=display-catalogue");
        exit();
    }
}
