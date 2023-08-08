<?php

namespace models;

use connexion\Database;

class Friend extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
    /**
     * Méthode permettant d'insérer une relation d'amitié en BDD
     *
     * @params int $id_sender l'émmetteur/expéditeur de la demande d'ami
     * @params int $id_receiver le destinataire
     * @params string $uniq_id un identifiant unique propre à chaque amitié
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function createFriendship(int $id_sender, int $id_receiver, string $uniq_id): bool
    {
        $query = $this -> database -> prepare('INSERT INTO `amis`
                                                    (`id_amitie`,
                                                     `id_user1`,
                                                     `id_user2`)
                                                VALUES
                                                     (:id_amitie, :id_user1_1, :id_user2_1), 
                                                     (:id_amitie, :id_user1_2, :id_user2_2)');
                                
        $test = $query->execute(array(
            'id_amitie' => $uniq_id,
            'id_user1_1' => $id_sender,
            'id_user2_1' => $id_receiver,
            'id_user1_2' => $id_receiver,
            'id_user2_2' => $id_sender
        ));
        
        return $test;
    }
    
    /**
     * Méthode permettant de récupérer les amis d'un utilisateur grâce à son identifiant
     *
     * @params int $id_user l'identifiant de l'utilisateur
     * 
     * @return array Renvoie un tableau des amis de l'utilisateur
     */
    public function getUsersFriends(int $id_user): array
    {
        $friends = null;
        
        $query = $this -> database -> prepare('SELECT
                                                    `id`,
                                                    `id_amitie`,
                                                    `id_user1`,
                                                    `id_user2`,
                                                    `pseudo`,
                                                    `pdp`
                                                FROM
                                                    `amis`
                                                INNER JOIN users ON amis.id_user2 = users.id_user
                                                WHERE amis.id_user1 = ?');
                                
        $test = $query->execute([$id_user]);
        
        $friends = $query -> fetchAll();
        
        return $friends;
    }
    
    /**
     * Méthode permettant de vérifier le lien d'amitié entre 2 utilisateurs
     *
     * @params int $id_user l'identifiant de l'utilisateur session
     * @params int $id_friend l'identifiant de l'autre utilisateur
     * 
     * @return boolean Renvoie true si les deux utilisateurs sont amis et false s'ils ne le sont pas
     */
    public function verifyFriendship(int $id_user, int $id_friend): bool
    {
        $friends = null;
        
        //avant même de faire la requête, vérifier si l'auteur de la publication = l'utilisateur session
        if($id_user == $id_friend)
        {
            return true;
        }
        
        $query = $this -> database -> prepare('SELECT *
                                                FROM amis
                                                WHERE (id_user1 = :id_user1 AND id_user2 = :id_user2)
                                                OR (id_user1 = :id_user2 AND id_user2 = :id_user1)');
                                
        $test = $query->execute([
            'id_user1' => $id_user,
            'id_user2' => $id_friend
        ]);
        
        $friends = $query -> fetchAll();
        
        if(!empty($friends))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Méthode permettant de récupérer les publications des amis d'un utilisateur grâce à son identifiant
     *
     * @params int $id_user l'identifiant de l'utilisateur
     * 
     * @return array Renvoie un tableau des publications des amis de l'utilisateur
     */
    public function getFriendsReviews(int $id_user): array
    {
        $friends_reviews = null;
        
        $query = $this -> database -> prepare('SELECT
                                                    `id`,
                                                    `id_amitie`,
                                                    `id_user1`,
                                                    `id_user2`,
                                                    `pseudo`,
                                                    `pdp`,
                                                    `id_review`,
                                                    contenu, 
                                                    note, 
                                                    date,
                                                    titre,
                                                    image,
                                                    release_date,
                                                    synopsis,
                                                    nom_cat
                                                FROM
                                                    `amis`
                                                INNER JOIN users ON amis.id_user2 = users.id_user
                                                INNER JOIN reviews ON amis.id_user2 = reviews.id_user
                                                INNER JOIN oeuvres ON reviews.id_oeuvre = oeuvres.id_oeuvre
                                                INNER JOIN categories ON oeuvres.id_categorie = categories.id_categorie
                                                WHERE
                                                    amis.id_user1 = ?
                                                ORDER BY date DESC');
                                
        $test = $query -> execute([$id_user]);
        
        $friends_reviews = $query -> fetchAll();
        
        return $friends_reviews;
    }
    
    /**
     * Méthode permettant de supprimer la relation d'amitié entre 2 utilisateurs
     *
     * @params int $id_user l'identifiant de l'utilisateur session
     * @params int $id_friend l'identifiant de l'autre utilisateur
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function deleteFriendshipBond(int $id_user, int $id_friend): bool
    {
        $query = $this -> database -> prepare('
                                        DELETE 
                                        FROM `amis`
                                        WHERE (`id_user1` = :id_user AND `id_user2` = :id_ami)
                                        OR (`id_user1` = :id_ami AND `id_user2` = :id_user)
                                ');
                                
       $test = $query->execute([
            'id_user' => $id_user,
            'id_ami' => $id_friend
        ]);
        
        return $test;
    }
}