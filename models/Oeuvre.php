<?php

namespace models;

use connexion\Database;

class Oeuvre extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
    /**
     * Méthode permettant de récupérer une oeuvre en fonction de son id unique verif_id (différent de l'id AI)
     *
     * @params int $id l'identifiant unique de l'oeuvre
     * 
     * @return array | bool Renvoie un tableau avec l'id AI de l'oeuvre si verif_id a été trouvé en BDD, et false si non
     */
    public function getOeuvreByVerifId(int $id): array | bool
    {
        $query = $this -> database -> prepare('SELECT
                                                    `id_oeuvre`
                                                FROM
                                                    `oeuvres`
                                                WHERE
                                                    `verif_id` = ?
                                        ');
                                        
                                        
        $query -> execute([$id]);
        
        $id = $query -> fetch();
        
        return $id; 
    }
    
    /**
     * Méthode permettant de récupérer la dernière oeuvre insérée en BDD
     *
     * 0 @params
     * 
     * @return int | null Renvoie le dernier identifiant inséré ou null s'il ne trouve rien
     */
    public function getLastInsertedId(): ?int
    {
        $query = $this -> database -> prepare('SELECT LAST_INSERT_ID() FROM oeuvres');
        
        $query -> execute();
        
        $lastInsertId = $query -> fetchColumn();
        
        if($lastInsertId !== false)
        {
            return $lastInsertId;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Méthode permettant d'insérer une oeuvre en BDD
     *
     * 6 @params
     * @params int $verif_id l'identifiant unique de l'oeuvre
     * @params string $titre le titre de l'oeuvre
     * @params string $poster le poster de l'oeuvre
     * @params string $release_date la date de sortie de l'oeuvre
     * @params string $synopsis le synopsis de l'oeuvre
     * @params int $id_categorie l'identifiant de la catégorie de l'oeuvre
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function addOeuvre(int $verif_id, string $titre, string $poster, string $release_date, string $synopsis, int $id_categorie): bool
    {
        $addOeuvre = null;
        
        $query = $this -> database -> prepare(' 
                                                INSERT INTO `oeuvres`(
                                                    `verif_id`,
                                                    `titre`,
                                                    `image`,
                                                    `release_date`,
                                                    `synopsis`,
                                                    `id_categorie`
                                                )
                                                VALUES(
                                                    :verif_id,
                                                    :titre,
                                                    :image,
                                                    :release_date,
                                                    :synopsis,
                                                    :id_categorie
                                                      )');
        $addOeuvre = $query -> execute(array(
              'verif_id' => $verif_id,
              'titre' => $titre,
              'image' => $poster,
              'release_date' => $release_date,
              'synopsis' => $synopsis,
              'id_categorie' => $id_categorie
              ));   
              
        return $addOeuvre; 
    }
    
    /**
     * Méthode permettant de récupérer toutes les oeuvres en BDD
     *
     * 0 @params
     * 
     * @return array Renvoie un tableau des oeuvres
     */
    public function getOeuvres(): array
    {
        $query = $this -> database -> prepare('
                                                SELECT
                                                    `id_oeuvre`,
                                                    `verif_id`,
                                                    `titre`,
                                                    `image`,
                                                    `release_date`,
                                                    `synopsis`,
                                                    nom_cat
                                                FROM
                                                    `oeuvres`
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                        ');
                                        
        $query -> execute();
        
        return $query -> fetchAll();
    }
    
    /**
     * Méthode permettant de récupérer toutes les oeuvres en BDD grâce à leur catégorie
     *
     * @params string $category la catégorie de l'oeuvre
     * 
     * @return array Renvoie un tableau des oeuvres en fonction de leur catégorie
     */
    public function getOeuvreByCategory(string $category): array
    {
        $query = $this -> database -> prepare('
                                                SELECT
                                                    `id_oeuvre`,
                                                    `verif_id`,
                                                    `titre`,
                                                    `image`,
                                                    `release_date`,
                                                    `synopsis`,
                                                    nom_cat
                                                FROM
                                                    `oeuvres`
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                WHERE 
                                                    nom_cat = ?
                                        ');
                                        
        $query -> execute([$category]);
        
        return $query -> fetchAll();
    }
}