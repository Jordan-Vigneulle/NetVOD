<?php

namespace iutnc\NetVOD\action;
use iutnc\NetVOD\auth\AuthProvider;

require_once 'vendor/autoload.php';

/*
 * Classe permettant de connecter / déconnecter un utilisateur en session
 *
 * */
class ConnectionUserAction extends Action {

    public function execute(): string
    {

        // vérification si l'utilisateur est déjà connecté
        if(!isset($_SESSION['user'])){
            // Pas connecté
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                // Affichage formulaire
                return <<<HTML
               <form method="post" action="?action=connexion">
               <div id="titleaction">Connexion</div>
               <input type="email" name="email" placeholder="Email">
               <input type="password" name="password" placeholder="Mot de passe">
               <br><br>
               <input type="submit" value="Connexion">
               </form>
            HTML;
            }
            else{
                // Traitement des valeurs
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $a = new AuthProvider();
                if($a->signin($email, $_POST['password'])){
                    return "<div class='message-info'>Bienvenue : $email </div>";
                }else{
                    return "<div class='message-info'>Utilisateur inconnu</div>";
                }
            }
        }else{
            // Si l'utilisateur n'est pas connecté
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return <<<HTML
               <form method="post" action="?action=connexion">
               <div id="titleaction">Voulez-vous vraiment vous déconnecter ?</div>
               <br><br>
               <input type="submit" value="Deconnexion">
               </form>
            HTML;
            }
            else{
                // On enlève les objets en session.
                $_SESSION['user'] = null;
                return "<div class='message-info'>Déconnexion effectué.</div>";
            }
        }
        return "";
    }
}
