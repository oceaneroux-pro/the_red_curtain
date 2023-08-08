<?php

namespace models;

use connexion\Database;

class Notification extends Database
{
    private $database;
    
    public function __construct()
    {
        $this -> database = $this -> getConnexion();
    }
    
    /**
     * Méthode permettant de récupérer les notifications de l'utilisateur grâce à son identifiant
     *
     * @params int $id_user l'identifiant de l'utilisateur
     * 
     * @return array Renvoie un tableau des notifications de l'utilisateur
     */
    public function getNotifsByUserId(int $id_user): array
    {
        $query = $this -> database -> prepare('SELECT
                                                    `id_notif`,
                                                    `id_receiver`,
                                                    `id_sender`,
                                                    `message`,
                                                    `type`,
                                                    `date`
                                                FROM
                                                    `notifications`
                                                WHERE
                                                    `id_receiver` = ?
                                                ');
                                        
        $query -> execute([$id_user]);
        
        $notifications = $query -> fetchAll();
        
        return $notifications;
    }
    
    /**
     * Méthode permettant d'insérer une notification en BDD
     *
     * 4 @params
     * @params int $id_receiver l'identifiant du destinataire de la notification
     * @params int $id_sender l'identifiant de l'expéditeur
     * @params string $message le contenu de la notification
     * @params string $type le type de la notification
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function sendNotif(int $id_receiver, int $id_sender, string $message, string $type): bool
    {
        $query = $this -> database -> prepare('INSERT INTO `notifications`(
                                                    `id_receiver`,
                                                    `id_sender`,
                                                    `message`,
                                                    `type`,
                                                    `date`
                                                )
                                                VALUES(
                                                    :id_receiver, 
                                                    :id_sender, 
                                                    :message,
                                                    :type,
                                                    NOW()
                                                )');
                                        
        $test = $query -> execute(array(
                    'id_receiver' => $id_receiver,
                    'id_sender' => $id_sender,
                    'message' => $message,
                    'type' => $type
              ));
              
        return $test;
    }
    
    /**
     * Méthode permettant de supprimer une notification en BDD
     *
     * @params int $id_notif l'identifiant de la notification
     * 
     * @return boolean Renvoie true ou false en fonction de si la requête a échouée ou non
     */
    public function deleteNotif(int $id_notif): bool
    {
        
        $query = $this -> database -> prepare('DELETE
                                                FROM
                                                    `notifications`
                                                WHERE
                                                    `id_notif` = ?
                                                ');
                                        
        $test = $query -> execute([$id_notif]);
        return $test;
    }
    
}