<?php

namespace models;

use connexion\Database;

class Admin extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
    /**
     * Méthode permettant d'insérer les données de l'administrateur en BDD, utilisée 1 fois
     *
     * 0 @params
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function createAdmin(): bool
    {
        $pseudo = "oceane";
        $hash = password_hash("mdp123", PASSWORD_DEFAULT);
        $uniq_id = uniqid();
        
        $query = $this -> database -> prepare('
                                        INSERT INTO `admin`(`pseudo`, `password`, `id_unique`)
                                        VALUES(
                                            :pseudo,
                                            :password,
                                            :id_unique)
                                ');
        $test = $query->execute(array(
               'pseudo' => $pseudo,
               'password' => $hash,
               'id_unique' => $uniq_id
               ));
        
        return $test; 
    }
    
    /**
     * Méthode permettant de récupérer les données de l'admin à partir de son identifiant unique 
     * (différent de l'id AI)
     *
     * @params int $id L'identifiant unique de l'admin (généré avec uniq_id)
     * 
     * @return boolean array | bool Renvoie un tableau ou false si l'admin a été trouvé ou non
     */
    public function getAdminByUniqId(string $id): array | bool
    {
        $query = $this -> database -> prepare('
                                        SELECT `id_admin`, `pseudo`, `password`, `id_unique`
                                        FROM admin
                                        WHERE id_unique = ?
                                ');
                                
        $query -> execute([$id]);
        
        return $query -> fetch();
    }
    
}