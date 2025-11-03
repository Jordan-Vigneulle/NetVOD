<?php
namespace iutnc\NetVOD\auth;

use iutnc\NetVOD\exception\AuthError;
use iutnc\NetVOD\repository\NetVODRepository;

class AuthProvider {
    public static function signin(string $email, string $passwd2check): bool {
        $df = NetVODRepository::getInstance();
        $hash = $df->getHashUser($email);
        if (!$hash || !password_verify($passwd2check, $hash)) {
            return false;
        }
        $_SESSION['user'] = $email;
        return true;
    }

    public static function register(): string
    {
        if (!isset($_POST['email'], $_POST['password'], $_POST['password2'])) {
            return "<div class='message-info'>Manque de données.</div>";
        }

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        if ($_POST['password'] !== $_POST['password2']) {
            return "<div class='message-info'>Les Mot de passe ne corresponds pas.</div>";
        }

        $repo = NetVODRepository::getInstance();

        if (!$repo->checkPasswordStrength($_POST['password'])) {
            return "<div class='message-info'>Mot de passe invalide</div>";
        }
        if (!$repo->checkUserConnect($email)) {
            return "<div class='message-info'>L'Utilisateur possède déjà un compte.</div>";
        }

        $repo->addUserBD($email, $_POST['password']);
        return "<div class='message-info'>Utilisateur ajouté avec succès.</div>";
    }
}