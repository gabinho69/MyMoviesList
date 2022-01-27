<?php

namespace App\Service;
use aharen\OMDbAPI;

class FilmDescriptionService
{
    private static $API_KEY = '42b0f7a1';
    public function getDescription()
    {
        $omdb = new OMDbAPI(self::$API_KEY);
        $resultat = $omdb->fetch('t', $_POST["movie"]["name"]);
        if($resultat->data->Response == 'False'){
            return "erreur lors de la recherche dans l'API";
        }
        else{
            return $resultat;
        }

    }
}