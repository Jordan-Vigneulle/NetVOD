<?php

namespace iutnc\NetVOD\action;

use iutnc\NetVOD\auth\AuthProvider;
use iutnc\NetVOD\repository\NetVODRepository;

require_once 'vendor/autoload.php';

/*
 * Classe permettant d'ajouter un utlisateur dans la base de données.
 *
 * */
class AddUserAction extends Action
{
    public function execute(): string
    {

        if(isset($_SESSION['user'])){
            return "<div class='message-info'>Vous êtes déjà connecté</div>";
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return <<<HTML
                <form method="post" action="?action=add-user">
                    <div id="titleaction">Inscription :</div>
                    <input type="nom" name="nom" placeholder="Nom" required>
                    <input type="nom" name="prenom" placeholder="Prénom" required>
                    <input type="email" name="email" placeholder="Adresse mail" required>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <input type="password" name="password2" placeholder="Confirmez votre mot de passe" required>
                    <input type="nom" name="carteB" placeholder="Numéro de Carte Bleue" required>
                    <input type="submit" value="Ajouter l'utilisateur">
                </form>
                    <br>
                    <div class='message-info'>* Contient au moins 10 caractères dont minimum un chiffre, une minuscule/majuscule et un caractère spéciale</div>
            HTML;
        }
        else{
            return AuthProvider::register();
        }
    }
}
