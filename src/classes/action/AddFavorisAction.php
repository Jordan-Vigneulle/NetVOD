<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\repository\NetVODRepository;

require_once 'vendor/autoload.php';

/*
 * Classe permettant d'ajouter une série en favoris
 *
 * */
class AddFavorisAction extends Action
{
    public function execute(): string
    {

        if (!isset($_SESSION['user'])) {
            return "<div class='message-info'>Vous devez vous connecter</div>";
        }
        $idSerie = filter_var($_GET['series_id'], FILTER_VALIDATE_INT);
        $r = NetVODRepository::getInstance();
        $verifSerie = $r->getSerie($idSerie)['titre'];
        if ($verifSerie === null) {
            return "Echec, cette série n'éxiste pas";
        }
        $resultat = $r->setSerieFavoris($idSerie, $_SESSION['user']);
        header("Location: ?action=display-catalogue");
        exit();
    }
}
