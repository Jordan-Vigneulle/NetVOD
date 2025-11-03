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
        <title>Deefy</title>
    </head>
    <body>
        <header>
            <a href="." id="none"><h1>NetVOD</h1></a>
            <nav>
                <ul>
                    <li><a href=".">Accueil</a></li>
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