<?php
namespace controllers;

use models\Admin;
use models\User;
use models\Review;
use models\Comment;
use controllers\SecurityController;

class AdminController extends SecurityController
{
    private Admin $admin;
    private User $user;
    private Review $review;
    private Comment $comment;
    
    public function __construct()
    {
        $this -> admin = new Admin();
        $this -> user = new User();
        $this -> review = new Review();
        $this -> comment = new Comment();
    }
    
    /**
     * Méthode permettant de comparer le formulaires aux infos dans la BDD pour autoriser l'admin à 
     * se connecter ou non
     **/
    public function connexionAdmin(): void
    {
        //j'ai créé mon admin manuellement une fois seulement
        // $create = $this -> admin -> createAdmin();
        
        if(isset($_POST['id_unique']) && !empty($_POST['id_unique'])
            && isset($_POST['mdp']) && !empty($_POST['mdp'])
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            $id = htmlspecialchars(trim($_POST['id_unique']));
            $mdp = htmlspecialchars(trim($_POST['mdp']));
            
            $verifId = $this -> admin -> getAdminByUniqId($id);
            
            if($verifId)
            {
                if(password_verify($mdp, $verifId['password']))
                {
                    $_SESSION['admin']['id_admin'] = $verifId['id_admin'];
                    $_SESSION['admin']['pseudo'] = $verifId['pseudo'];
                    $_SESSION['admin']['password'] = $verifId['password'];
                    $_SESSION['admin']['id_unique'] = $verifId['id_unique'];
                    
                    header('location:index.php?action=admin-panel');
                    exit();
                }
                else
                {
                    $message = "Votre mot de passe est incorrect";
                }
                
            }
            else
            {
                $message = "Votre identifiant est incorrect";
            }
        }
        
        $template = "admin/login";
        require "views/layout-admin.phtml";
    }
    
    /**
     * Méthode permettant de détruire la session et déconnecter l'admin
     **/
    public function deconnexionAdmin(): void
    {
        session_destroy();
        header("location:index.php?action=admin");
    }
    
    /**
     * Méthode permettant de d'afficher tous les utilisateurs dans manage-users
     **/
    public function displayAllUsers(): void
    {
        $users = $this -> user -> getAllUsers();
        
        $template = "admin/manage-users";
        require "views/layout-admin.phtml";
    }
    
    /**
     * Méthode permettant de filtrer les utilisateurs en fonction de leur dernière date de 
     * connexion en commençant par les plus récente
     **/
    public function findUsersByRecentLogin()
    {
        $users = $this -> user -> getUsersByLastLogin();
        echo json_encode($users);
    }
    
    /**
     * Méthode permettant de filtrer les utilisateurs en fonction de leur dernière date de 
     * connexion en commençant par les plus anciennes
     **/
    public function findUsersByOldLogin()
    {
        $direction = "<";
        $users = $this -> user -> getUsersByLastLogin($direction);
        echo json_encode($users);
    }
    
    /**
     * Méthode permettant de filtrer les utilisateurs en fonction de s'ils ont déjà posté ou non
     **/
    public function findUsersWithNoPosts()
    {
        $users = $this -> user -> getUsersWithNoPosts();
        echo json_encode($users);
    }
    
    /**
     * Méthode permettant de consulter le profil d'un utilisateur
     **/
    public function manageProfile(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id']))
        {
            $id_user = $_GET['id'];
            
            $_SESSION['admin']['current_id'] = $id_user;
            
            $user = $this -> user -> getUsersInfoById($id_user);
            
            $comments = $this -> comment -> getCommentsByUserId($id_user);
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=admin-panel&message=$message");
            exit(); 
        }
        
        $template = "admin/manage-profile";
        require "views/layout-admin.phtml";
    }
    
    /**
     * Méthode permettant d'afficher toutes les publications
     **/
    public function managePosts(): void
    {
        $reviews = $this -> review -> getReviews();
        
        $template = "admin/manage-posts";
        require "views/layout-admin.phtml";
    }
    
    /**
     * Méthode permettant d'afficher les publications en fonction de la date souhaitée
     **/
    public function filterReviewsByDate(): void
    {
        if(isset($_GET['date']) && !empty($_GET['date']))
        {
            $date = $_GET['date'];
            $reviews = $this -> review -> getReviewsByDate($date);
            echo json_encode($reviews);
        }
    }
    
    /**
     * Méthode permettant de gérer le formulaire de suppression d'une publication
     **/
    public function confirmDeletePostAdmin(): void
    {
        if(isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id']))
        {
            $id_review = $_GET['id'];
            
            //je récupère la publication
            $review = $this -> review -> getReviewById($id_review);
            
            if($review != false)
            {
                $message = 
                    "Cher utilisateur,\n\nNous avons récemment supprimé votre review sur ".$review['titre']. " car elle a enfreint nos règles d'utilisation.\n\nCette décision a été prise pour maintenir un environnement sûr et respectueux pour tous nos utilisateurs. Si vous avez des questions ou des préoccupations, n'hésitez pas à nous contacter à theredcurtain@contact.com. Nous vous remercions de votre compréhension et de votre coopération.\n\nCordialement,\nL'équipe the red curtain.";
                
                if(isset($_POST['textarea']) && !empty($_POST['textarea'])
                    && isset($_POST['id_user']) && !empty($_POST['id_user']) && is_numeric($_POST['id_user'])
                    && $_POST['token'] == $_SESSION['csrf_token'])
                {
                    if($id_review == $_SESSION['admin']['current_id'])
                    {
                        $delete = $this -> review -> deleteReview($id_review);
                    
                        $confirmation = "La publication a bien été supprimée";
                        header("location:index.php?action=manage-posts&message=$confirmation");
                        exit();
                    }
                    else
                    {
                        $confirmation = "Une erreur est survenue";
                        header("location:index.php?action=manage-posts&message=$confirmation");
                        exit(); 
                    }
                }
            }
            
            $template = "admin/confirm-delete-post";
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
     * Méthode permettant de gérer le formulaire de suppression d'un commentaire
     **/
    public function confirmDeleteCommentAdmin(): void
    {
        if(isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id']))
        {
            $id_comment = $_GET['id'];
            
            $comment = $this -> comment -> getCommentById($id_comment);
            
            $message = 
            "Cher utilisateur,\n\nNous souhaitons vous informer que nous avons récemment supprimé l'un de vos commentaires qui a été jugé comme étant offensant et en violation de nos règles d'utilisation.\n\nNous vous rappelons que nous encourageons les échanges constructifs et le respect mutuel entre les utilisateurs. Veuillez prendre en compte ces principes lors de vos futures interactions.\n\nSi vous avez des questions ou des préoccupations supplémentaires concernant cette suppression, n'hésitez pas à nous contacter à theredcurtain@contact.com.\n\nCordialement,\nL'équipe the red curtain";
            
            if(isset($_POST['textarea']) && !empty($_POST['textarea'])
            && isset($_POST['id_user']) && !empty($_POST['id_user']) && is_numeric($_POST['id_user'])
            && isset($_POST['id_comment']) && !empty($_POST['id_comment']) && is_numeric($_POST['id_comment'])
            && $_POST['token'] == $_SESSION['csrf_token'])
            {
                $id_comment = trim(htmlspecialchars($_POST['id_comment']));
                
                if($id_comment == $_GET['id'])
                {
                    $delete = $this -> comment -> deleteComment($id_comment);
                    $confirmation = "Le commentaire a bien été supprimé";
                    header("location:index.php?action=manage-comments&message=$confirmation");
                    exit();
                }
                else if ($id_comment != $_GET['id'])
                {
                    $warning = "Une erreur est survenue";
                    header("location:index.php?action=delete-comment-admin&id=$id_comment&message=$warning");
                    exit();
                }
            }
            
            $template = "admin/confirm-delete-comment";
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
     * Méthode permettant de gérer la suppression d'un utilisateur
     **/
    public function confirmDeleteUser(): void
    {
        if(array_key_exists('id',$_GET) && !empty($_GET['id']) && is_numeric($_GET['id']))
        {
            $id_user = $_GET['id'];
            
            $user = $this -> user -> getUsersInfoById($id_user);
            
            //si le formumlaire est soumis, je n'ai rien à récupérer
            if ($_SERVER['REQUEST_METHOD'] === 'POST'
                && $_POST['token'] == $_SESSION['csrf_token'])
            {
                $pdp = $user['pdp'];
                
                if($id_user == $_SESSION['admin']['current_id'])
                {
                    //si l'image est autre que les avatars de base, alors supprimer
                    if($pdp == 'blue.png' || $pdp == 'pink.png' || $pdp == 'yellow.png' || $pdp == 'green.png')
                    {
                        $delete = $this -> user -> deleteUserById($id_user);
                        
                        $message="L'utilisateur a bien été supprimé";
                    
                        header("location:index.php?action=manage-users&message=$message");
                        exit();
                    }
                    else
                    {
                        $delete = $this -> user -> deleteUserById($id_user);
                        
                        unlink('public/images/avatars/'.$pdp);
                        
                        $message="L'utilisateur a bien été supprimé";
                    
                        header("location:index.php?action=manage-users&message=$message");
                        exit();
                    }
                }
                else
                {
                    $message = "Une erreur est survenue";
                    header("location:index.php?action=manage-users&message=$message");
                    exit(); 
                }
            }
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=admin-panel&message=$message");
            exit(); 
        }
        
        $template = "admin/delete-profile";
        require "views/layout-admin.phtml";
    }
}