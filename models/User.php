<?php

namespace models;

use connexion\Database;

class User extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
     /**
     * Méthode permettant d'insérer un nouvel utilisateur dans la BDD
     *
     * @param string $pseudo le pseudo de l'utilisateur.
     * @param string $randomPdp le nom de la photo de profil de l'utilisateur.
     * @param string $anniversaire la date d'anniversaire de l'utilisateur.
     * @param string $mail l'adresse mail de l'utilisateur.
     * @param string $mdp le mot de passe de l'utilisateur.
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function addUser(string $pseudo, string $randomPdp, string $anniversaire, string $mail, string $mdp): bool
    {
        $user = null;
        
        $query = $this -> database -> prepare('
                                            INSERT INTO `users`(
                                                `pseudo`,
                                                `pdp`,
                                                `anniversaire`,
                                                `mail`,
                                                `mdp`
                                            )
                                            VALUES(
                                                :pseudo,
                                                :pdp,
                                                :anniversaire,
                                                :mail,
                                                :mdp
                                            )
                                                ');
    
        $test = $query -> execute(array(
            
               'pseudo' => $pseudo,
               'pdp' => $randomPdp,
               'anniversaire' => $anniversaire,
               'mail' => $mail,
               'mdp' => $mdp
               ));
        
        return $test; 
    }
    
    /**
     * Méthode permettant de vérifier si l'adresse mail (unique) existe déjà en BDD
     *
     * @param string $mail l'adresse mail de l'utilisateur.
     * 
     * @return boolean Renvoie true ou false si l'adresse mail a été trouvée en BDD
     */
    public function verifMail(string $mail): bool
    {
        $query = $this -> database -> prepare('
                                        SELECT `mail`
                                        FROM users
                                        WHERE mail = ?
                                ');
        $query -> execute([$mail]);
        
        $user = $query -> fetch();
        
        if(!empty($user))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Méthode permettant de vérifier si le pseudo (unique) de l'utilisateur existe déjà en BDD 
     *
     * @param string $pseudo le pseudo de l'utilisateur.
     * 
     * @return boolean Renvoie true ou false si le pseudo a été trouvé en BDD
     */
    public function verifPseudo(string $pseudo): bool
    {
        $query = $this -> database -> prepare('
                                        SELECT `pseudo`
                                        FROM users
                                        WHERE pseudo = ?
                                ');
                                
        $query -> execute([$pseudo]);
        
        $user = $query -> fetch();
        
        if(!empty($user))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Méthode permettant de récupérer les informations d'un utilisateur grâce à son mail
     * au moment de la connexion
     *
     * @param string $mail l'adresse mail de l'utilisateur.
     * 
     * @return array | bool Renvoie un tableau si l'adresse mail a été trouvée ou false si non
     */
    public function getUserByEmail(string $mail): array | bool
    {
        $query = $this -> database -> prepare('
                                        SELECT `mail`, `mdp`,`id_user`,`nom`,`prenom`,`pseudo`, `pdp`
                                        FROM users
                                        WHERE mail = ?
                                ');
        $query -> execute([$mail]);
        
        $user = $query -> fetch();
        
        return $user;
    }
    
    /**
     * Méthode permettant de récupérer les informations d'un utilisateur grâce à son id
     *
     * @param int $id l'adresse mail de l'utilisateur.
     * 
     * @return array | bool Renvoie un tableau si l'utilisateur a été trouvé ou false si non
     */
    public function getUsersInfoById(int $id): array | bool
    {
        $query = $this -> database -> prepare('
                                        SELECT
                                            `id_user`,
                                            `pseudo`,
                                            `pdp`,
                                            `mail`,
                                            `mdp`,
                                            `nom`,
                                            `prenom`,
                                            `anniversaire`,
                                            `tel`
                                        FROM
                                            `users`
                                        WHERE
                                            `id_user` = ?
                                ');
        $query -> execute([$id]);
        
        $user = $query -> fetch();
        
        return $user;
    }
    
    /**
     * Méthode permettant de récupérer l'id, le pseudo et la photo de profil d'un utilisateur grâce à son pseudo
     * // requête AJAX
     *
     * @param string $pseudo le pseudo de l'utilisateur recherché
     * 
     * @return array Renvoie un tableau des utilisateurs dont le pseudo contient des occurences
     * du pseudo entré en paramètres (LIKE)
     */
    public function getUserByPseudo(string $pseudo): array
    {
        $query = $this -> database -> prepare('
                                            SELECT
                                                `id_user`,
                                                `pseudo`,
                                                `pdp`
                                            FROM
                                                `users`
                                            WHERE 
                                                LOWER(`pseudo`) LIKE LOWER(?)
                                        ');
                                        
        $query -> execute(['%' . $pseudo . '%']);
    
        $users = $query -> fetchAll();
    
        return $users;
    }
    
    /**
     * Méthode permettant de modifier les informations de l'utilisateur en BDD
     *
     * 7 @params
     * @param string $nom le nom de l'utilisateur.
     * @param string $prenom le prénom de l'utilisateur.
     * @param string $anniversaire la date de naissance de l'utilisateur.
     * @param string $tel le téléphone de l'utilisateur.
     * @param string $pdp la photo de profil de l'utilisateur.
     * @param string $mail l'adresse mail de l'utilisateur.
     * @param int $id_user l'identifiant de l'utilisateur.
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function updateUserInfos(string $nom, string $prenom, string $anniversaire, string $tel, string $pdp, string $mail, int $id_user): bool
    {
        $query = $this -> database -> prepare('
                                            UPDATE
                                                `users`
                                            SET
                                                `nom` = :nom,
                                                `prenom` = :prenom,
                                                `anniversaire` = :anniversaire,
                                                `tel` = :tel,
                                                `pdp` = :pdp,
                                                `mail` = :mail
                                            WHERE 
                                                `id_user` = :id_user
                                                        ');
        
        $test = $query -> execute(array(
        
              'nom' => $nom,
              'prenom' => $prenom,
              'anniversaire' => $anniversaire,
              'tel' => $tel,
              'pdp' => $pdp,
              'mail' => $mail,
              'id_user' => $id_user
              ));
              
        return $test;
    }
    
    /**
     * Méthode permettant de modifier la date de connexion de l'utilisateur session à chaque fois qu'il se
     * connecte
     *
     * @param int $id_user l'identifiant de l'utilisateur.
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function trackLastLogin(int $id_user): bool
    {
        $query = $this -> database -> prepare('
                                            UPDATE
                                                `users`
                                            SET
                                                `last_login` = NOW()
                                            WHERE 
                                                `id_user` = ?
                                                        ');
        
        $test = $query -> execute([$id_user]);
        
        return $test;
    }
    
    /**
     * Méthode permettant de récupérer tous les utilisateurs en BDD
     *
     * 0 @param
     * 
     * @return array Renvoie un tableau des utilisateurs
     */
    public function getAllUsers(): array
    {
        $query = $this -> database -> prepare('
                                        SELECT
                                            `id_user`,
                                            `pseudo`,
                                            `pdp`,
                                            `mail`,
                                            `mdp`,
                                            `nom`,
                                            `prenom`,
                                            `anniversaire`,
                                            `tel`,
                                            `last_login`
                                        FROM
                                            `users`
                                ');
        $query -> execute();
        
        return $query -> fetchAll();
    }
    
    /**
     * Méthode permettant de récupérer les utilisateurs en fonction de leur dernière date de connexion(sur 2ans)
     *
     * @param $direction la direction dans laquelle les résultats doivent être triés
     * ">" = DESC = plus récent
     * "<" = ASC = plus ancien
     * 
     * @return array Renvoie un tableau des utilisateurs classés par leur date de connexion
     */
    public function getUsersByLastLogin($direction = ">"): array
    {
        $query = $this -> database -> prepare('
                                        SELECT
                                            `id_user`,
                                            `pseudo`,
                                            `pdp`,
                                            `mail`,
                                            `mdp`,
                                            `nom`,
                                            `prenom`,
                                            `anniversaire`,
                                            `tel`,
                                            `last_login`
                                        FROM
                                            `users`
                                        WHERE
                                        `last_login` '.$direction.'= DATE_SUB(NOW(), INTERVAL 2 YEAR)
                                ');
        $query -> execute();
        
        return $query -> fetchAll();
    }
    
    /**
     * Méthode permettant de récupérer les utilisateurs qui n'ont aucune publication
     *
     * 0 @params
     * 
     * @return array Renvoie un tableau des utilisateurs qui n'ont jamais rien publié sur le site
     */
    public function getUsersWithNoPosts(): array
    {
        $query = $this -> database -> prepare('
                                        SELECT
                                            `id_user`,
                                            `pseudo`,
                                            `pdp`,
                                            `mail`,
                                            `mdp`,
                                            `nom`,
                                            `prenom`,
                                            `anniversaire`,
                                            `tel`,
                                            `last_login`
                                        FROM
                                            `users`
                                        WHERE NOT EXISTS (
                                            SELECT 1
                                            FROM `reviews`
                                            WHERE `reviews`.`id_user` = `users`.`id_user`)
                                ');
        $query -> execute();
        
        return $query -> fetchAll();
    }
    
    /**
     * Méthode permettant de supprimer un utilisateur grâce à son id
     *
     * @params int $id_user l'identifiant de l'utilisateur
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function deleteUserById(int $id_user): bool
    {
        $query = $this -> database -> prepare('
                                        DELETE
                                        FROM
                                            `users`
                                        WHERE
                                            `id_user` = ?
                                ');
                                
        $test = $query -> execute([$id_user]);
        
        return $test;
    }
}




