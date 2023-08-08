<?php

namespace models;

use connexion\Database;

class Comment extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
    /**
     * Méthode permettant d'insérer un commentaire en BDD
     *
     * @params int $id_review l'identifiant de la review
     * @params int $id_user l'identifiant de l'utilisateur
     * @params string $comment le commentaire
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function insertComment(int $id_review, int $id_user, string $comment): bool
    {
        $query = $this -> database -> prepare('
                                            INSERT INTO `commentaires`(
                                                `id_review`,
                                                `id_user`,
                                                `contenu`,
                                                `date`
                                            )
                                            VALUES(
                                                :id_review,
                                                :id_user,
                                                :contenu,
                                                NOW()
                                            )
                                                ');
    
        $test = $query -> execute(array(
               'id_review' => $id_review,
               'id_user' => $id_user,
               'contenu' => $comment,
               ));
        
        return $test; 
    }
    
    /**
     * Méthode permettant de récupérer les commentaires d'une publication grâce à son id
     *
     * @params int $id_review l'identifiant de la publication (review)
     * 
     * @return boolean @return array | bool Renvoie un tableau des commentaires si la review a été trouvée ou false si non
     */
    public function getComments(int $id_review): array | bool
    {
        $query = $this -> database -> prepare('
                                            SELECT
                                                `id_comment`,
                                                `id_review`,
                                                commentaires.`id_user`,
                                                pseudo,
                                                pdp,
                                                `contenu`,
                                                `date`
                                            FROM
                                                `commentaires`
                                            INNER JOIN users ON commentaires.id_user = users.id_user
                                            WHERE
                                                `id_review` = ?
                                                ');
    
        $test = $query -> execute([$id_review]);
        
        return $query -> fetchAll();
    }
    
    /**
     * Méthode permettant de récupérer les commentaires d'un utilisateur grâce à son identifiant
     *
     * @params int $id_review l'identifiant de l'utilisateur
     * 
     * @return array | bool Renvoie un tableau des commentaires de l'utilisateur si celui ci a été trouvé ou false si non
     */
    public function getCommentsByUserId(int $id_user): array | bool
    {
        $query = $this -> database -> prepare('
                                            SELECT
                                                commentaires.`id_comment`,
                                                commentaires.`id_review`,
                                                commentaires.`id_user`,
                                                commentaires.`contenu`,
                                                commentaires.`date`,
                                                oeuvres.titre,
                                                reviews.id_review
                                            FROM
                                                `commentaires`
                                            INNER JOIN reviews ON commentaires.id_review = reviews.id_review
                                            INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                            WHERE
                                                commentaires.id_user = ?
                                                ');
    
        $test = $query -> execute([$id_user]);
        
        return $query -> fetchAll();;
    }
    
    /**
     * Méthode permettant de supprimer un commentaire grâce à son identifiant
     *
     * @params int $id_comment l'identifiant du commentaire
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function deleteComment(int $id_comment): bool
    {
        $query = $this -> database -> prepare('
                                            DELETE
                                            FROM
                                                `commentaires`
                                            WHERE
                                                id_comment = ?
                                                ');
    
        $test = $query -> execute([$id_comment]);
        
        return $query -> fetch();
    }
    
    // récupère tous les commentaires, peu importe l'utilisateur ou la publication
    /**
     * Méthode permettant de récupérer tous les commentaires, peu importe l'utilisateur ou la publication
     *
     * 0 @params
     * 
     * @return boolean Renvoie un tableau de tous les commentaires en BDD
     */
    public function getAllComments(): array
    {
        $query = $this -> database -> prepare('
                                            SELECT
                                                `id_comment`,
                                                `id_review`,
                                                commentaires.`id_user`,
                                                `contenu`,
                                                `date`,
                                                `pseudo`,
                                                users.`pdp`
                                            FROM
                                                `commentaires`
                                            INNER JOIN users ON commentaires.id_user = users.id_user
                                            ');
        $test = $query -> execute();
        
        return $query -> fetchAll();
    }
    
    /**
     * Méthode permettant de récupérer tous les commentaires contenant des occurances du mot 
     * transmis en paramètres
     *
     * @params string $word le mot recherché
     * 
     * @return array Renvoie un tableau des commentaires contenant le mot cherché
     */
    public function getCommentsByWordSearch(string $word): array
    {
        $query = $this -> database -> prepare('
                                            SELECT
                                                `id_comment`,
                                                `id_review`,
                                                commentaires.`id_user`,
                                                `contenu`,
                                                `date`,
                                                users.pseudo,
                                                users.`pdp`
                                            FROM
                                                `commentaires`
                                            INNER JOIN users ON commentaires.id_user = users.id_user
                                            WHERE 
                                                LOWER(`contenu`) LIKE LOWER(?)
                                        ');
                                        
        //permet de ressortir toutes les occurances de ce qu'a récupéré $word
        $query -> execute(['%' . $word . '%']);
    
        $comments = $query -> fetchAll();
        
        return $comments;
    }
    
    /**
     * Méthode permettant de récupérer un commentaire grâce à son identifiant
     *
     * @params int $id_comment l'identifiant du commentaire
     * 
     * @return array | bool Renvoie un tableau des informations du commentaire si celui ci a été trouvé ou false si non
     */
    public function getCommentById(int $id_comment): array | bool
    {
        $query = $this -> database -> prepare('
                                            SELECT
                                                `id_comment`,
                                                `id_review`,
                                                commentaires.`id_user`,
                                                `contenu`,
                                                `date`,
                                                pseudo
                                            FROM
                                                `commentaires`
                                            INNER JOIN users ON commentaires.id_user = users.id_user
                                            WHERE
                                                id_comment = ?
                                            ');
        $test = $query -> execute([$id_comment]);
        
        return $query -> fetch();
    }
}