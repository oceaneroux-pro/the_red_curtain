<?php

namespace models;

use connexion\Database;

class Review extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
    /**
     * Méthode permettant de récupérer toutes les reviews en BDD
     *
     * 0 @params
     * 
     * @return array Renvoie un tableau des reviews
     */
    public function getReviews(): array
    {
        $reviews = null;
        
        $query = $this -> database -> prepare('
                                                SELECT
                                                    reviews.id_review,
                                                    reviews.`id_user`,
                                                    `pseudo`,
                                                    `pdp`,
                                                    reviews.`id_oeuvre`,
                                                    `titre`,
                                                    `image`, 
                                                    `release_date`,
                                                    `note`,
                                                    `contenu`,
                                                    `date`,
                                                    categories.`nom_cat`
                                                FROM
                                                    `reviews`
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN users ON reviews.id_user = users.id_user
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                ORDER BY date DESC
                                        '); 
        $query -> execute();
        
        $reviews = $query -> fetchAll();
        
        return $reviews; 
    }
    
    /**
     * Méthode permettant de récupérer une review grâce à son identifiant
     *
     * @params int $id_review l'identifiant de la review
     * 
     * @return array | bool Renvoie un tableau des informations de la review si trouvée ou false si non
     */
    public function getReviewById(int $id_review): array | bool
    {
        $query = $this -> database -> prepare('
                                              SELECT
                                                    `id_review`,
                                                    `note`,
                                                    `contenu`,
                                                    `date`,
                                                    reviews.`id_user`,
                                                    `pdp`,
                                                    `pseudo`,
                                                    reviews.`id_oeuvre`,
                                                    `titre`,
                                                    `image`, 
                                                    `release_date`,
                                                    `synopsis`,
                                                    `note`,
                                                    `contenu`,
                                                    categories.`nom_cat`
                                                FROM
                                                    `reviews`
                                                INNER JOIN users ON reviews.id_user = users.id_user
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                WHERE
                                                    `id_review` = ?
                                        ');
                                        
        $query -> execute([$id_review]);
        
        return $query -> fetch();
    }
    
    /**
     * Méthode permettant d'insérer une review en BDD
     *
     * 5 @params
     * @params int $id_user l'identifiant de l'utilisateur auteur de la review
     * @params int $id_oeuvre l'identifiant de l'oeuvre sur laquelle la review se porte
     * @params int $note la note attribuée à l'oeuvre par l'utilisateur
     * @params string $contenu le contenu de la review, l'avis de l'utilisateur
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function insertReview(int $id_user, int $id_oeuvre, int $note, string $contenu): bool
    {
        $insertReview = null;
       
        $query = $this -> database -> prepare(' 
                                                INSERT INTO `reviews`(
                                                    `id_user`,
                                                    `id_oeuvre`,
                                                    `note`,
                                                    `contenu`,
                                                    `date`
                                                )
                                                VALUES(
                                                    :id_user,
                                                    :id_oeuvre,
                                                    :note,
                                                    :contenu,
                                                    NOW()
                                                      )');
        $insertReview = $query -> execute(array(
              'id_user' => $id_user,
              'id_oeuvre' => $id_oeuvre,
              'note' => $note,
              'contenu' => $contenu
              ));   
               
        return $insertReview;
    }
    
    /**
     * Méthode permettant de récupérer les reviews d'un utilisateur en fonction de son identifiant
     *
     * @params int $id l'identifiant de l'utilisateur
     * 
     * @return array Renvoie un tableau regroupant toutes les reviews de cet utilisateur par date la + récente
     */
    public function getReviewsByUserId(int $id) : array
    {
        $reviews = null;
        
        $query = $this -> database -> prepare('
                                                SELECT
                                                    reviews.`id_user`,
                                                    `pseudo`,
                                                    `pdp`,
                                                    reviews.`id_oeuvre`,
                                                    `titre`,
                                                    `image`, 
                                                    `release_date`,
                                                    reviews.id_review,
                                                    `note`,
                                                    `contenu`,
                                                    `date`,
                                                    categories.`nom_cat`
                                                FROM
                                                    `reviews`
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN users ON reviews.id_user = users.id_user
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                WHERE reviews.id_user = ?
                                                ORDER BY `date` DESC
                                        '); 
        $query -> execute([$id]);
        
        $reviews = $query -> fetchAll();
        
        return $reviews;
    }
    
    /**
     * Méthode permettant de récupérer les reviews d'utilisateurs qui ne sont PAS amis avec l'utilisateur session 
     * grâce à l'identifiant de cet utilisateur
     *
     * @params int $id_user l'identifiant de l'utilisateur
     * 
     * @return array Renvoie un tableau regroupant toutes les reviews des utilisateur avec lesquels l'utilisateur 
     * n'est pas ami
     */
    public function getOthersReviews(int $id_user) : array
    {
        $reviews = null;
        
        $query = $this -> database -> prepare('SELECT
                                                    `id_review`,
                                                    reviews.`id_user`,
                                                    `pseudo`,
                                                    `pdp`,
                                                    `note`,
                                                    `contenu`,
                                                    `date`,
                                                    titre,
                                                    image,
                                                    release_date,
                                                    synopsis,
                                                    nom_cat
                                                FROM
                                                    `reviews`
                                                INNER JOIN users ON reviews.id_user = users.id_user
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                LEFT JOIN amis ON reviews.id_user = amis.id_user2 AND amis.id_user1 = ?
                                                WHERE
                                                    reviews.id_user != ? AND amis.id_user1 IS NULL
                                                ORDER BY `date` DESC
                                                ');
                
        $test = $query -> execute([$id_user, $id_user]);
        
        $reviews = $query -> fetchAll();
        
        return $reviews;
    }
    
    /**
     * Méthode permettant de modifier les informations d'une review en BDD
     *
     * @params int $id_review l'identifiant de la review
     * @params int $note la note attribuée à l'oeuvre par l'utilisateur
     * @params string $contenu le contenu de la review, l'avis de l'utilisateur
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function updateReview(int $id_review, int $note, string $contenu): bool
    {
        $query = $this -> database->prepare('
                                            UPDATE
                                                `reviews`
                                            SET
                                                `note` = :note,
                                                `contenu` = :contenu,
                                                `date` = NOW()
                                            WHERE
                                                `id_review` = :id_review
                                                        ');
        
        $test = $query -> execute(array(
            
                'id_review' => $id_review,            
                'note' => $note,
                'contenu' => $contenu
              ));
              
        return $test;
    }
    
    /**
     * Méthode permettant de supprimer une review en BDD grâce à son identifiant
     *
     * @params int $id_review l'identifiant de la review
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function deleteReview(int $id_review): bool
    {
        $query = $this -> database -> prepare('DELETE
                                                FROM
                                                    `reviews`
                                                WHERE
                                                    `id_review` = ?
                                                ');
                                        
        $test = $query -> execute([$id_review]);
        
        return $test;
    }
    
    /**
     * Méthode permettant de récupérer les reviews en fonction de la date donnée en params
     *
     * @params string $date une date au format YYYY-mm-dd
     * 
     * @return array Renvoie un tableau des reviews ayant été créées à la date donnée en params
     */
    public function getReviewsByDate(string $date): array
    {
        $reviews = null;
        
        $query = $this -> database -> prepare('
                                                SELECT
                                                    reviews.id_review,
                                                    reviews.`id_user`,
                                                    `pseudo`,
                                                    `pdp`,
                                                    reviews.`id_oeuvre`,
                                                    `titre`,
                                                    `image`, 
                                                    `release_date`,
                                                    `note`,
                                                    `contenu`,
                                                    `date`,
                                                    categories.`nom_cat`
                                                FROM
                                                    `reviews`
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN users ON reviews.id_user = users.id_user
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                WHERE 
                                                    DATE(date) = ?
                                        '); 
        $query -> execute([$date]);
        
        $reviews = $query -> fetchAll();
        
        return $reviews; 
    }

    /**
     * Méthode permettant de vérifie si l'utilisateur n'a pas déjà publié son avis sur une oeuvre
     * et si oui, l'empêcher de re-publier son avis
     *
     * @params int $id_user l'identifiant de l'utilisateur
     * @params int $id_oeuvre l'identifiant de l'oeuvre
     * 
     * @return array | bool Renvoie un tableau de la review si trouvée ou false si non
     */
    public function isAlreadyReviewed(int $id_user, int $id_oeuvre): array | bool
    {
        $review = null;
        
        $query = $this -> database -> prepare('SELECT
                                                    reviews.`id_user`,
                                                    reviews.`id_oeuvre`,
                                                    reviews.id_review,
                                                    date
                                                FROM
                                                    `reviews`
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN users ON reviews.id_user = users.id_user
                                                WHERE 
                                                    reviews.id_user = ? AND reviews.id_oeuvre = ?
                                                ');
                
        $test = $query -> execute([$id_user, $id_oeuvre]);
        
        $review = $query -> fetch();
        
        return $review;
    }
    
}