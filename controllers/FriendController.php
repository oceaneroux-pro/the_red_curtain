<?php
namespace controllers;

use models\Friend;
use controllers\SecurityController;

class FriendController extends SecurityController{
    
    private Friend $friend;
    
    public function __construct()
    {
        $this -> friend = new Friend();
    }
    
    /**
     * Méthode permettant de gérer l'ajout d'amis dans les notifications
     **/
    public function insertFriendship(): void
    {
        if(isset($_POST['id_notif']) && !empty($_POST['id_notif'])
            && isset($_POST['id_sender']) && !empty($_POST['id_sender']))
        {
            $id_receiver = $_POST['id_sender'];
            $id_sender = $_SESSION['user']['id_user'];
            $uniq_id = uniqid();

            // vérifier s'ils ne sont pas déjà amis dans le cas où il y a une "double demande"
            $verifFriendship = $this -> friend -> verifyFriendship($id_receiver, $id_sender);
            
            if($verifFriendship == false)
            {
                $friendship = $this -> friend -> createFriendship($id_sender, $id_receiver, $uniq_id);
            
                $message = "Vous êtes amis désormais :)";
                header("location:index.php?action=see-notifs&message=$message");
                exit();
            }
            else
            {
                $message = "Une erreur est survenue";
                header("location:index.php?action=see-notifs&message=$message");
                exit();
            }
        }
    }
    
    /**
     * Méthode permettant de récupérer les publications des amis de l'utilisateur session
     **/
    public function displayFriendsReviews(): void
    {
        $id_user = $_SESSION['user']['id_user'];
    
        $friends_reviews = $this -> friend -> getFriendsReviews($id_user);
        
        $template = "views/user/feed";
        require "views/layout.phtml";
    }
    
    /**
     * Méthode permettant de gérer le formulaire de suppression d'amitié qui se présente sous 
     * la forme d'un bouton sur le profil utilisateur
     **/
    public function destroyFriendship()
    {
        $id_user = $_SESSION['user']['id_user'];
        
        $friends = $this -> friend -> getUsersFriends($id_user);
        
        $verifId = array_column($friends, 'id_user2');
        
        if(isset($_POST['id_user_session']) && !empty($_POST['id_user_session'])
            && isset($_POST['pseudo_user_session']) && !empty($_POST['pseudo_user_session'])
            && isset($_POST['id_ami']) && !empty($_POST['id_ami'])
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            $id_sender = htmlspecialchars(trim($_POST['id_user_session']));
            $id_receiver = htmlspecialchars(trim($_POST['id_ami']));
            
            $sender_pseudo = htmlspecialchars(trim($_POST['pseudo_user_session']));
            
            if($id_sender == $_SESSION['user']['id_user']
                && $sender_pseudo == $_SESSION['user']['pseudo']
                && in_array($id_receiver,$verifId))
            {
                $destroy = $this -> friend -> deleteFriendshipBond($id_sender,$id_receiver);
                
                $message = "Vous n'êtes plus amis désormais :(";
                header("location:index.php?action=see-others&id=$id_receiver&message=$message");
                exit();
            }
            else
            {
                $message = "Une erreur est survenue";
                header("location:index.php?action=feed&message=$message");
                exit();
            }
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=feed&message=$message");
            exit();
        }
    }
    
    
}