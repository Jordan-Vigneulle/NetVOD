<?php

namespace iutnc\NetVOD\action;

/*
 * Classe lancée quand on appuie sur le lien mot de passe oublié
 *
 */

use iutnc\NetVOD\repository\NetVODRepository;

class MDPOublieAction extends Action
{

    public function execute(): string
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // Affichage du formulaire
            return <<<HTML
               <form method="post" action="?action=mdpoublie">
               <div id="titleaction">Connexion</div>
               <input type="email" name="email" placeholder="Email">
               <br><br>
               <input type="submit" value="Valider">
               </form>
            HTML;
        } else {
            // Traitement des valeurs
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            // Création du token et update du token
            $repo = NetVODRepository::getInstance();
            $token = bin2hex(random_bytes(32));
            $possible = $repo->addToken($email, $token);

            //On montre le lien
            if ($possible) {
                $html = "<div class='message-info'>Pour pouvoir changer votre mot de passe, appuyez sur le lien</div>";
                $html .= "<a href='?action=changermdp&token={$token}&user={$email}' class='btn-auth'>Cliquez ici</a>";
            } else {
                $html = "<div class='message-info'>Compte inexistant</div>";
            }
            return $html;
        }
    }
}