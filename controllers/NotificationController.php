<?php
namespace controllers;

use models\Notification;
use controllers\SecurityController;

class NotificationController extends SecurityController
{
    private Notification $notification;
    
    public function __construct()
    {
        $this -> notification = new Notification();
    }
    
    /**
     * Méthode permettant de récupérer les notifications de l'utilisateur
     **/
    public function displayNotifs(): void
    {
        $id_user = $_SESSION['user']['id_user'];
        
        $notifications = $this -> notification -> getNotifsByUserId($id_user);
        
        $template = "user/notifications";
        require "views/layout.phtml";
    }
    
    /**
     * Méthode permettant de gérer le formulaire d'envoi d'une demande d'ami qui se présente 
     * sous la forme d'un bouton sur le profil utilisateur
     **/
    public function addFriend(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id']))
        {
            $id_friend = $_GET['id'];
        
            if(isset($_POST['id_user_session']) && !empty($_POST['id_user_session'])
                && isset($_POST['id_ami']) && !empty($_POST['id_ami'])
                && isset($_POST['pseudo_user_session']) && !empty($_POST['pseudo_user_session'])
                && $_POST['token'] == $_SESSION['csrf_token']) 
            {
                $id_sender = htmlspecialchars(trim($_POST['id_user_session']));
                $id_receiver = htmlspecialchars(trim($_POST['id_ami']));
                
                $sender_pseudo = htmlspecialchars(trim($_POST['pseudo_user_session']));
                
                $message = "<a href='index.php?action=see-others&id=".$id_sender."'/>".$sender_pseudo."</a> vous a envoyé une demande d'ami.";
                $type = "demande";
                
                if($id_sender == $_SESSION['user']['id_user']
                    && $sender_pseudo == $_SESSION['user']['pseudo']
                    && $id_receiver == $id_friend)
                {
                    $add = $this -> notification -> sendNotif($id_receiver, $id_sender, $message, $type);
                
                    $message = "Votre demande d'ajout a bien été envoyée";
                    header("location:index.php?action=see-others&id=$id_receiver&message=$message");
                    exit();
                }
                else
                {
                    $message = "Une erreur est survenue";
                    header("location:index.php?action=see-others&id=$id_friend&warning=$message");
                    exit();
                }
            }
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=feed&message=$message");
            exit();
        }
    }
    
    /**
     * Méthode permettant de gérer le formulaire d'acceptation de la demande d'ami qui se présente 
     * sous la forme d'un bouton dans les notifications
     **/
    public function acceptFriend(): void
    {
        $id_user = $_SESSION['user']['id_user'];
        
        //récupère les notifs afin de comparer les inputs aux données en BDD
        $notifications = $this -> notification -> getNotifsByUserId($id_user);
        //récupère que les id des notifs
        $verifId = array_column($notifications, 'id_notif');
        //récupère que les id de la colonne des expéditeurs
        $verifSender = array_column($notifications, 'id_sender');
        
        if(isset($_POST['id_notif']) && !empty($_POST['id_notif'] && is_numeric($_POST['id_notif']))
            && isset($_POST['id_sender']) && !empty($_POST['id_sender'] && is_numeric($_POST['id_sender']))
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            $id_notif = $_POST['id_notif'];
            $id_receiver = $_POST['id_sender'];
            $message =  $_SESSION['user']['pseudo']." a accepté votre demande.";
            $type = "ajout";
            
            if(in_array($id_notif,$verifId)
                && in_array($id_receiver,$verifSender))
            {
                //envoie la notification d'acceptation à l'autre utilisateur
                $accept = $this -> notification -> sendNotif($id_receiver, $id_user, $message, $type);
                //supprime la notif d'ajout en BDD
                $delete = $this -> notification -> deleteNotif($id_notif);
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
     * Méthode permettant de supprimer la demande dans la BDD = équivaut à un refus car le lien d'amitié ne se créer pas
     **/
    public function refuseFriend(): void
    {
        $id_user = $_SESSION['user']['id_user'];
        
        //récupère les notifs afin de comparer les inputs aux données en BDD
        $notifications = $this -> notification -> getNotifsByUserId($id_user);
        
        //récupère que les id
        $verifId = array_column($notifications, 'id_notif');
        
        
        if(isset($_POST['id_notif']) && !empty($_POST['id_notif'] && is_numeric($_POST['id_notif']))
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            $id_notif = trim($_POST['id_notif']);
            
            if(in_array($id_notif,$verifId))
            {
                $refuse = $this -> notification -> deleteNotif($id_notif);
                
                $message = "Demande correctement supprimée";
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
     * Méthode permettant d'envoyer une notification lorsqu'un utilisateur a commenté sous une publication
     **/
    public function notifyComment(): void
    {
        if(isset($_POST['commentaire']) && !empty($_POST['commentaire'])
            && isset($_POST['id_user']) && !empty($_POST['id_user'] && is_numeric($_POST['id_user']))
            && isset($_POST['id_review']) && !empty($_POST['id_review']) && is_numeric($_POST['id_review']))
        {
            //récup le sender
            $id_sender = $_SESSION['user']['id_user'];
            $sender_pseudo = $_SESSION['user']['pseudo'];
            //le receiver
            $id_receiver = $_POST['id_user'];
            //la review sur laquelle le sender a commenté
            $id_review = $_POST['id_review'];
            
            $message =  "<a href='index.php?action=see-others&id=".$id_sender."'>".$sender_pseudo."</a> a commenté votre <a href='index.php?action=see-review&id=".$id_review."&user=".$id_receiver."'>publication</a>";
            $type = "commentaire";
            
            // éviter que l'utilisateur reçoit une notif s'il commente son propre post
            if($id_sender !=  $id_receiver)
            {
                $notify = $this -> notification -> sendNotif($id_receiver, $id_sender, $message, $type);
            }
            
        }
    }
    
    /**
     * Méthode permettant compter les notifications qu'a reçu l'utilisateur session dans la BDD 
     * pour afficher un compteur
     * 
     * s'actualise à chaque début de session "user"
     * 
     **/
     public function countNotifs(): ?int
    {
        $id_user = $_SESSION['user']['id_user'];
        
        $notifications = $this -> notification -> getNotifsByUserId($id_user);
        
        $total = count($notifications);
        
        $_SESSION['user']['notifs'] = $total;
        
        return $total;
    }
    
    /**
     * Méthode permettant de gérer le formulaire d'envoi de notification/avertissement de la part
     * d'un admin qui aura toujours l'id 0
     **/
    public function sendAdminNotif(): void
    {
        if(isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id']))
        {
            $id_receiver = $_GET['id'];
            
            if(isset($_POST['textarea']) && !empty($_POST['textarea'])
                && $_POST['token'] == $_SESSION['csrf_token'])
            {
                $id_sender = 0;
                $message = $_POST['textarea'];
                $type = "avertissement";
                
                if($id_receiver == $_SESSION['admin']['current_id'])
                {
                    $notify = $this -> notification -> sendNotif($id_receiver, $id_sender, $message, $type);
                    
                    $confirmation = "L'avertissement a bien été envoyé";
                    header("location:index.php?action=manage-users-profile&id=$id_receiver&message=$confirmation");
                    exit(); 
                }
                else
                {
                    $confirmation = "Une erreur est survenue";
                    header("location:index.php?action=manage-users-profile&id=".$_SESSION['admin']['current_id']."&message=$confirmation");
                    exit(); 
                }
            }
            
            $template = "admin/send-notif";
            require "views/layout-admin.phtml";
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=admin-panel&message=$message");
            exit(); 
        }
    }
    
    /**
     * Méthode permettant de gérer le formulaire d'envoi de notification/avertissement lors de la 
     * suppression d'une publication/commentaire
     **/
    public function sendDeletedNotif(): void
    {
        if(isset($_POST['textarea']) && !empty($_POST['textarea'])
            && isset($_POST['id_user']) && !empty($_POST['id_user']) && is_numeric($_POST['id_user']))
        {
            $id_receiver = $_POST['id_user'];
            $id_sender = 0;
            $message = $_POST['textarea'];
            $type = "avertissement";
            
            $notify = $this -> notification -> sendNotif($id_receiver, $id_sender, $message, $type);
        }
    }
}