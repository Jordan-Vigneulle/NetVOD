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
            $html .= "<br><br>";
            $html .= "<div class='profilepicture-container'>";
            $repo = NetVODRepository::getInstance();
            $photos = $repo->getPhotoProfileALL();
            foreach ($photos as $photo) {
                    $html .= "<div class='profilepicture'>";
                    $html .= "<a href ='?action=modif-user&profile_piture={$photo['idPhoto']}'><img src=src/style/img/profilepicture/{$photo['img']} alt={$photo['idPhoto']}>";
                    $html .= "</div>";
                }

        }
        $html .= "</div>";
            return $html;
        }
}