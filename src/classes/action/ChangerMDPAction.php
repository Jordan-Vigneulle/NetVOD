<?php

namespace iutnc\NetVOD\action;

/*
 * Classe pour changer le mot de passe
 */

use iutnc\NetVOD\repository\NetVODRepository;

class ChangerMDPAction extends Action
{

    public function execute(): string
    {
        if($_SERVER["REQUEST_METHOD"] == "GET") {
            // Formulaire pour changer le mot de passe
            return <<<HTML
                <form method="post" action="?action=add-user">
                    <div id="titleaction">Inscription :</div>
                    <input type="password" name="password" placeholder="Mot de passe*" required>
                    <input type="password" name="password2" placeholder="Confirmez votre mot de passe" required>
                    <input type="submit" value="Changer le mot de passe">
                </form>
                    <br>
                    <div class='message-info'>*Le mot de passe doit contenir au moins 10 caractères dont minimum un chiffre, une minuscule/majuscule et un caractère spéciale</div>
            HTML;
        }else{
            // On vérifie si c'est le même mot de passe
            if ($_POST['password'] !== $_POST['password2']) {
                return "<div class='message-info'>Les Mot de passe ne correspond pas.</div>";
            }

            // On change le mot de passe
            $repo = NetVODRepository::getInstance();
            $repo->updateMDP($_GET['$user'],$_GET['$mdp']);

            return <<<HTML
                      <div class='message-info'>Votre mot de passe a bien été changé</div>  
                      HTML;

        }
    }
}