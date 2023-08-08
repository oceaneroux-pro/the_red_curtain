<?php
namespace controllers;

use models\Review;
use models\Oeuvre;
use models\User;
use models\Friend;
use models\Comment;
use controllers\SecurityController;

class ReviewController extends SecurityController{
    
    private Review $review;
    private Oeuvre $oeuvre;
    private User $user;
    private Friend $friend;
    private Comment $comment;
    
    public function __construct()
    {
        $this -> review = new Review();
        $this -> oeuvre = new Oeuvre();
        $this -> user = new User();
        $this -> friend = new Friend();
        $this -> comment = new Comment();
    }
    
    /**
     * Méthode permettant d'afficher les barres de recherches des oeuvres, 
     * c'est dans ce template que la relation aux APIs se fait
     **/
    public function showSearchBoxes()
    {
        $template = "review/search-boxes";
        require "views/layout.phtml";
    }
    
    // gère le formulaire d'ajout d'une review (critique)
    public function addReview()
    {
        if(isset($_POST['note']) && !empty($_POST['note'])
            && isset($_POST['contenu']) && !empty($_POST['contenu'])
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            //assigne le type récupéré dans le formulaire à la clé lui correspondant dans la table categories
            $categories = [
                'movie' => 1,
                'tv' => 2,
                'book' => 3,
            ];
            
            //récupère la valeur correspondante
            $id_categorie = $categories[$_POST['type']];
            
            $titre = htmlspecialchars(trim($_POST['titre']));
            $release_date = htmlspecialchars(trim($_POST['date']));
            $poster = htmlspecialchars(trim($_POST['poster']));
            $note = htmlspecialchars(trim($_POST['note']));
            $contenu = htmlspecialchars(trim($_POST['contenu']));
            $verif_id = htmlspecialchars(trim($_POST['verif_id']));
            
            // pour les films/séries, récupérer le synopsis
            if($id_categorie == 1 || $id_categorie == 2)
            {
                $synopsis = htmlspecialchars(trim($_POST['synopsis']));
            }
            else // pour les livres, l'API ne propose pas de synopsis
            {
                $synopsis = "pas de synopsis disponible";
            }
            
            $id_user = $_SESSION['user']['id_user'];
            $date = date('d-m-Y');
            
            //vérifie si l'oeuvre n'existe pas déjà en BDD grâce aux identifiants propres à chaque oeuvre stocké dans $verif_id
            $verifID = $this -> oeuvre -> getOeuvreByVerifId($verif_id); //récupère l'id
            
            if($verifID != false) // s'il trouve une correspondance
            {
                $id_oeuvre = $verifID['id_oeuvre'];
                
                //vérifie si l'utilisateur n'a pas déjà laissé une review sur l'oeuvre
                $alreadyReviewed = $this -> review -> isAlreadyReviewed($id_user, $id_oeuvre);
                
                //s'il récupère un tableau alors redirection vers la publication en question
                if($alreadyReviewed != false)
                {
                    $id_review = $alreadyReviewed['id_review'];
                    
                    $alreadyReviewed['date'] = date("d/m/Y", strtotime($alreadyReviewed['date']));
                    
                    $message = "Vous avez déjà laissé une review sur cette oeuvre le ".$alreadyReviewed['date'].".";
                    
                    header("location:index.php?action=see-review&id=$id_review&user=$id_user&alert=$message");
                    exit();
                }
                else
                {
                    //si la note est numérique et inférieure/égale à 5 = ok
                    if(is_numeric($note) && $note <= 5)
                    {
                        $tableReviews = $this -> review -> insertReview($id_user,$id_oeuvre,$note,$contenu,$date);
                        // redirection vers les barres de recherches
                        $message = "Votre review a bien été prise en compte";
                        header("location:index.php?action=search&message=$message");
                        exit();
                    }
                    else
                    {
                       $message = "Une erreur est survenue";
                        header("location:index.php?action=search&message=$message");
                        exit(); 
                    }
                    
                    
                }
            } 
            else //s'il n'y a pas de correspondance, enregsitre l'oeuvre puis l'id_oeuvre dont on a besoin = le dernier id enregistré
            {
                if(is_numeric($note) && $note <= 5)
                {
                    //enregistre l'oeuvre
                    $tableOeuvres = $this -> oeuvre -> addOeuvre($verif_id,$titre,$poster,$release_date,$synopsis,$id_categorie);
                    
                    //grace à lastInsertId, récupère l'oeuvre tout juste enregistrée
                    $id_oeuvre =  $this -> oeuvre -> getLastInsertedId();
                    
                    //ajoute la review sur la bonne oeuvre
                    $tableReviews = $this -> review -> insertReview($id_user,$id_oeuvre,$note,$contenu,$date);
                    
                    //redirection vers les barres de recherches
                    $message = "Votre review a bien été prise en compte";
                    header("location:index.php?action=search&message=$message");
                    exit();
                }
                else
                {
                    $message = "Une erreur est survenue";
                    header("location:index.php?action=search&message=$message");
                    exit(); 
                }
                
            }
        }
        
        $template = "review/add-review";
        require "views/layout.phtml";
    }
    
    /**
     * Méthode permettant de récupérer les publications de l'utilisateur session
     * 
     * AJAX, côté USER
     * 
     **/
    public function displayUsersOwnReviewsAjax()
    {
        $id_user = $_SESSION['user']['id_user'];
        
        $own_reviews = $this -> review -> getReviewsByUserId($id_user);
        
        echo json_encode($own_reviews);
    }
    
    /**
     * Méthode permettant de récupérer les publications d'autres utilisateurs avec qui l'utilisateur 
     * session n'est PAS amis
     * 
     * AJAX, côté USER
     * 
     **/
    public function displayOthersReviews()
    {
        $id_user = $_SESSION['user']['id_user'];
        
        $others_reviews = $this -> review -> getOthersReviews($id_user);
        
        echo json_encode($others_reviews);
    }
    
    /**
     * Méthode permettant de récupérer une review avec son id + gérer le formulaire
     * d'envoi de commentaire
     **/
    public function displayOneReview(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id'])
            && array_key_exists('user',$_GET) && is_numeric($_GET['user']))
        {
            $id_review = $_GET['id'];
            $id_user = $_SESSION['user']['id_user']; 
            
            $review = $this -> review -> getReviewById($id_review);
            $comments = $this -> comment -> getComments($id_review);
            
            if($review)
            {
                $id_author = $review['id_user'];
                //vérifier le lien entre les 2 utilisateurs pour voir s'ils sont autorisés à communiquer
                $verifFriendship = $this -> friend -> verifyFriendship($id_user, $id_author);
                
                if(isset($_POST['commentaire']) && !empty($_POST['commentaire'])
                    && isset($_POST['id_user']) && !empty($_POST['id_user'])
                    && isset($_POST['id_review']) && !empty($_POST['id_review'])
                    && $_POST['token'] == $_SESSION['csrf_token'])
                {
                    $comment = htmlspecialchars($_POST['commentaire']);
                    $id_user = $_SESSION['user']['id_user'];
                    $id_review = htmlspecialchars($_POST['id_review']);
                    $id_writer =  htmlspecialchars($_POST['id_user']);
                    
                    if($id_review == $review['id_review']
                        && $id_writer == $review['id_user'])
                    {
                        $sendComment = $this -> comment -> insertComment($id_review, $id_user, $comment);
                       
                        if($sendComment)
                        {
                            $message = "Votre commentaire a bien été envoyé.";
                        }
                        else
                        {
                            $message = "Une erreur est survenue";
                        }
                        
                        header("location:index.php?action=see-review&id=$id_review&user=$id_writer&message=$message");
                        exit();  
                    }
                    else
                    {
                        $message = "Une erreur est survenue";
                        header("location:index.php?action=see-review&id=".$review['id_review']."&user=$id_writer&message=$message");
                        exit();  
                    }
                }
            }
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=feed&message=$message");
            exit();
        }
        
        $template = "review/review";
        require "views/layout.phtml";
        
    }
    
    /**
     * Méthode permettant de gérer le formulaire de modification de review
     **/
    public function modifyReview(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id']))
        {
            $id_review = $_GET['id'];
            
            $review = $this -> review -> getReviewById($id_review);
            
            $id_user = $review['id_user'];
            
            //si l'auteur de la review != l'utilisateur session alors il n'a pas accès la review
            if($id_user == $_SESSION['user']['id_user'])
            {
                if(isset($_POST['contenu']) && !empty($_POST['contenu'])
                && isset($_POST['note']) && !empty($_POST['note'])
                && $_POST['token'] == $_SESSION['csrf_token']
                && $id_review == $_POST['id_review']
                && $_POST['id_review'] == $review['id_review'])
                {
                    $note = htmlspecialchars(trim($_POST['note']));
                    $contenu = htmlspecialchars(trim($_POST['contenu']));
                    
                    if(is_numeric($note) && $note <= 5)
                    {
                        $update = $this -> review -> updateReview($id_review, $note, $contenu);
                        
                        $message = "Le changement a bien été pris en compte";
                        //redirection vers le profil
                        header("location:index.php?action=see-profile&message=$message");
                        exit();
                    }
                    else
                    {
                        $alert = "Une erreur est survenue";
                    }
                }
            }
            else
            {
                $message = "Une erreur est survenue";
                header("location:index.php?action=see-profile&warning=$message");
                exit();
            }
            
            $template = "review/modify-review";
            require "views/layout.phtml"; 
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=feed&message=$message");
            exit();
        }
        
    }
    
    /**
     * Méthode permettant de gérer le formulaire de suppression de review
     **/
    public function confirmDeleteReview(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id']))
        {
            $id_review = $_GET['id'];
            
            $review = $this -> review -> getReviewById($id_review);
            
            if(isset($_POST['id_review']) && !empty($_POST['id_review']) && is_numeric($_POST['id_review']) 
                && $_POST['token'] == $_SESSION['csrf_token'])
            {
                if($id_review == $_POST['id_review']
                && $_POST['id_review'] == $review['id_review'])
                {
                   $id_user = $_SESSION['user']['id_user'];
                
                    $delete = $this -> review -> deleteReview($id_review);
                    
                    $message = "Votre review a bien été supprimée";
                    header("location:index.php?action=see-profile&message=$message");
                    exit(); 
                }
                else
                {
                    $message = "Une erreur est survenue lors de la suppression de la review";
                    header("location:index.php?action=see-profile&message=$message");
                    exit();
                }
                
            }
            
            $template = "review/delete-review";
            require "views/layout.phtml"; 
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=feed&message=$message");
            exit();
        }
        
    }
    
    /**
     * Méthode permettant de récupérer une review et ses commentaires avec son id
     * 
     * côté ADMIN
     * 
     **/
    public function manageOneReview(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id']))
        {
            $id_review = $_GET['id'];
            
            $_SESSION['admin']['current_id'] = $id_review;
            
            $review = $this -> review -> getReviewById($id_review);
            
            $comments = $this -> comment -> getComments($id_review);
            
            $template = "admin/manage-review";
            require "views/layout-admin.phtml"; 
        }
        else
        {
            $message = "Une erreur est survenue";
            header("location:index.php?action=admin-panel&message=$message");
            exit();
        }
    }
    
}