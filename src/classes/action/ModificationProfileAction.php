<?php

namespace iutnc\NetVOD\action;
use iutnc\NetVOD\repository\NetVODRepository;

class ModificationProfileAction extends Action
{

    public function execute(): string
    {
        if(isset($_SESSION['user'])){
            $html="";
            $html .= "<h2 id='titleaction'>Photo de profil</h2>";
            $html .= "<div class='profilepicture-container'>";
            $repo = NetVODRepository::getInstance();
            $photos = $repo->getPhotoProfileALL();
            foreach ($photos as $photo) {
                    $html .= "<div class='profilepicture'>";
                    $html .= "<a href ='?action=modif-user&profile_picture={$photo['idPhoto']}'><img src=src/style/img/profilepicture/{$photo['img']} alt={$photo['idPhoto']}>";
                    $html .= "</div>";
                }
            if(isset($_GET['profile_picture'])){
                $repo = NetVODRepository::getInstance();
                $repo->setPhotoProfile($_SESSION['user'], $_GET['profile_picture']);
            }
            $html .= "</div>";
        }else{
            $html = "<h2 id='message-info'>Vous n'êtes pas connecté</h2>";
        }
            return $html;
        }
}