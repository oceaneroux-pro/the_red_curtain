<?php
namespace controllers;

use models\Oeuvre;
use controllers\SecurityController;

class OeuvreController extends SecurityController
{
    private Oeuvre $oeuvre;
    
    public function __construct()
    {
        $this -> oeuvre = new Oeuvre();
    }
    
    /**
     * Méthode permettant de récupérer toutes les oeuvres en BDD
     **/
    public function displayOeuvres(): void
    {
        $oeuvres = $this -> oeuvre -> getOeuvres();
        
        $template = "admin/catalogue";
        require "views/layout-admin.phtml";
    }
    
    /**
     * Méthode permettant de récupérer les oeuvres en fonction de leur type = SÉRIE
     * 
     * AJAX, côté ADMIN
     * 
     **/
    public function displaySeries()
    {
        $category = "Série";
        $oeuvres = $this -> oeuvre -> getOeuvreByCategory($category);
        $total = count($oeuvres);
        
        $response = 
        [
            "total" => $total,
            "oeuvres" => $oeuvres
        ];
        
        echo json_encode($response);
    }
    
    /**
     * Méthode permettant de récupérer les oeuvres en fonction de leur type = FILM
     * 
     * AJAX, côté ADMIN
     * 
     **/
    public function displayFilms()
    {
        $category = "Film";
        $oeuvres = $this -> oeuvre -> getOeuvreByCategory($category);
        $total = count($oeuvres);
        
        $response = 
        [
            "total" => $total,
            "oeuvres" => $oeuvres
        ];
        
        echo json_encode($response);
    }
    
    /**
     * Méthode permettant de récupérer les oeuvres en fonction de leur type = LIVRE
     * 
     * AJAX, côté ADMIN
     * 
     **/
    public function displayLivres()
    {
        $category = "Livre";
        $oeuvres = $this -> oeuvre -> getOeuvreByCategory($category);
        $total = count($oeuvres);
        
        $response = 
        [
            "total" => $total,
            "oeuvres" => $oeuvres
        ];
        
        echo json_encode($response);
    }
}