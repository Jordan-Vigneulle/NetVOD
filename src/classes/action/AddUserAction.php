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
        session_start();
        if(isset($_SESSION['user'])){
            return "<div class='message-info'>Vous êtes déjà connecté</div>";
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return <<<HTML
                <form method="post" action="?action=add-user">
                    <div id="titleaction">Inscription :</div>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <input type="password" name="password2" placeholder="Répétez le mot de passe" required>
                    <input type="submit" value="Ajouter l'utilisateur">
                </form>
            HTML;
        }
        else{
            return AuthProvider::register();
        }
    }
}
