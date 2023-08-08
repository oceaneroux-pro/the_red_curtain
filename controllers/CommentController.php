<?php
namespace controllers;

use models\Comment;
use controllers\SecurityController;

class CommentController extends SecurityController
{
    private Comment $comment;
    
    public function __construct()
    {
        $this -> comment = new Comment();
    }
    
    /**
     * Méthode permettant de gérer le formulaire d'envoi de commentaire en BDD
     **/
    public function sendComment(): void
    {
        if(isset($_POST['commentaire']) && !empty($_POST['commentaire'])
            && isset($_POST['id_user']) && !empty($_POST['id_user'])
            && isset($_POST['id_review']) && !empty($_POST['id_review'])
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            // echo "coucou";
            $comment = htmlspecialchars($_POST['commentaire']);
            $id_user = $_SESSION['user']['id_user'];
            $id_review = htmlspecialchars($_POST['id_review']);
            $id_writer =  htmlspecialchars($_POST['id_user']);
            
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
    }
    
    /**
     * Méthode permettant de récupérer tous les commentaires (ADMIN)
     **/
    public function getEveryComment(): void
    {
        $comments = $this -> comment -> getAllComments();
        
        $length = count($comments);
        
        $template = "admin/manage-comments";
        require "views/layout-admin.phtml";
        
    }
    
    /**
     * Méthode permettant de filtrer les commentaires en fonction du mot recherché = gérer le langage utilisé
     * AJAX, côté ADMIN
     **/
    public function filterComments()
    {
        if(isset($_GET['data']) && !empty($_GET['data']))
        {
            $word = trim(htmlspecialchars($_GET['data']));
            
            $comments = $this -> comment -> getCommentsByWordSearch($word);
            
            echo json_encode($comments);
        }
    }
}