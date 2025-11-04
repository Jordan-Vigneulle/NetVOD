<?php


namespace iutnc\NetVOD\dispatch;

use iutnc\NetVOD\action as a;
require_once 'vendor/autoload.php';


class Dispatcher{

    private string $action;

    public function __construct(string $s){
        $this->action = $s;
    }

    public function run(){
        $html = "";
        switch($this->action){
            case 'add-user':
                $html = (new a\AddUserAction())->execute();
                break;
            case 'connexion':
                $html = (new a\ConnectionUserAction())->execute();
                break;
            case 'display-catalogue':
                $html = (new a\DisplayCatalogue())->execute();
                break;
            case 'display-series':
                $html = (new a\DisplaySeries())->execute();
                break;
            case 'lecture-series':
                $html = (new a\DisplayEpisodeAction())->execute();
                break;
            default:
                $html = (new a\DefaultAction())->execute();
                break;
        }
        $this->renderPage($html);
    }


    public function renderPage(string $s): void{
        echo <<<Limite
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="src/style/css/style.css">
        <title>NetVOD</title>
    </head>
    <body>
        <header>
            <a href="." id="none"><img id="logo" src="src\style\img\logo.png" alt="NetVOD"</a>
            <nav>
                <ul>
                    <li><a href=".">Accueil</a></li>
                    <li><a href="?action=add-user">Inscription</a></li>
                    <li><a href="?action=connexion">Connexion</a></li>
                    <li><a href="?action=display-catalogue">Catalogue</a></li>
                </ul>
            </nav>
        </header>
        <main>
            {$s}
        </main>
        <footer>
            <p>&copy; 2025 NetVOD - Tous droits réservés</p>
        </footer>
    </body>
    </html>
    Limite;
    }


}