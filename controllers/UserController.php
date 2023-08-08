<?php
namespace controllers;

use models\User;
use models\Review;
use models\Friend;
use controllers\SecurityController;

class UserController extends SecurityController{
    
    private User $user;
    private Review $review;
    private Friend $friend;
    
    public function __construct()
    {
        $this -> user = new User();
        $this -> review = new Review();
        $this -> friend = new Friend();
    }
    
    /**
     * Méthode permettant de gérer le formumlaire de créatip, d'un compte utilisateur
     **/
    public function createAccount(): void
    {
        if(isset($_POST['pseudo']) && !empty($_POST['pseudo']) 
              && isset($_POST['mail']) && !empty($_POST['mail'])
              && isset($_POST['anniversaire']) && !empty($_POST['anniversaire'])
              && isset($_POST['mdp']) && !empty($_POST['mdp'])
              && $_POST['token'] == $_SESSION['csrf_token'])
        {
            $inputs = 
            [
                "pseudo" => trim($_POST['pseudo']),
                "mail" => trim($_POST['mail']),
                "mdp" => trim($_POST['mdp']),
                "anniversaire" => trim($_POST['anniversaire'])
            ];
            
            $errors = $this -> verifyInputs($inputs);
            $messages = [];
    
            if (!empty($errors))
            {
                foreach ($errors as $error)
                {
                     array_push($messages, $error);
                }
                
            } 
            else if(empty($errors))
            {
                $pseudo = $inputs["pseudo"];
                $mail = $inputs["mail"];
                $anniversaire = $inputs["anniversaire"];
                $mdp = $inputs["mdp"];
                $mdp = password_hash($mdp,PASSWORD_DEFAULT);
                
                //affecte un avatar random lors de la création du compte
                $photosDeProfil = ['blue.png', 'pink.png', 'yellow.png', 'green.png'];
                
                // -1 car les tableaux commencent à 0
                $random = rand(0, count($photosDeProfil) - 1);
                
                $randomPdp = $photosDeProfil[$random];
                
                // le mail et le pseudo sont uniques
                $verifMail = $this -> user -> verifMail($mail);
                $verifPseudo = $this -> user -> verifPseudo($pseudo);
                
                if($verifMail == false && $verifPseudo == false)
                {
                    $test = $this -> user -> addUser($pseudo, $randomPdp, $anniversaire, $mail, $mdp);
                    
                    if($test)
                    {
                        $message = "Votre compte a bien été créé :)";
                    }
                    else
                    {
                        $message = "Une erreur innatendue est survenue :( Veuillez réessayer";
                    }
                }
                else
                {
                    if($verifPseudo == true)
                    {
                        $message = "Ce pseudo est déjà utilisé :(";
                    }
                    else
                    {
                        $message = "Un compte est déjà associé à cette adresse mail :/";
                    }
                }
            }
        }
        
        $template = "views/homepage";
        require "views/layout.phtml";
    }
    
    /**
     * Méthode permettant de comparer le formulaires aux infos dans la BDD pour autoriser 
     * l'utilisateur à se connecter ou non
     **/
    public function connexion(): void
    {
         if(isset($_POST['mail']) && !empty($_POST['mail'])
            && isset($_POST['mdp']) && !empty($_POST['mdp'])
            && $_POST['token'] == $_SESSION['csrf_token'])
        {
            $mail = trim($_POST['mail']); // récup mail
            $mdp = trim($_POST['mdp']); // récup mdp
            
            $verifMail = $this -> user -> getUserByEmail($mail);
            
            if($verifMail != false)
            {
                if(password_verify($mdp, $verifMail['mdp']))
                {
                    $_SESSION['user']['mail'] = $mail;
                    $_SESSION['user']['id_user'] = $verifMail['id_user'];
                    $_SESSION['user']['pseudo'] = $verifMail['pseudo'];
                    $_SESSION['user']['pdp'] = $verifMail['pdp'];
                    
                    //garder une trace de la dernière connexion dans la BDD pour l'admin
                    $id_user = $_SESSION['user']['id_user'];
                    $login = $this -> user -> trackLastLogin($id_user);
                    
                    // redirection vers le feed
                    header('location:index.php?action=feed');
                    exit();
                }
                else
                {
                    $message = "Votre mot de passe est incorrect :/";
                }
            }
            else
            {
                $message = "Aucun compte n'est associé à cette adresse mail :(";
            }
        }
    
        $template = "user/login";
        require "views/layout.phtml";
    }
    
    /**
     * Méthode permettant de détruire la session et déconnecter l'utilisateur
     **/
    public function deconnexion(): void
    {
        session_destroy();
        header("location:index.php?action=login");
    }
    
    /**
     * Méthode permettant de récupérer les informations de l'utilisateur dans son profil
     **/
     public function displayUserInfos(): void
    {
        $id_user = $_SESSION['user']['id_user'];
        
        //une partie de la vue est réservée à l'utilisateur     
        $user = $this -> user -> getUsersInfoById($id_user); 
        //l'autre partie est réservée à ses reviews
        $reviews = $this -> review -> getReviewsByUserId($id_user);
        //une partie réservée à ses amis
        $friends = $this -> friend -> getUsersFriends($id_user);
        
        if($user != false)
        {
            if(!isset($user['nom']) && empty($user['nom'])
            || !isset($user['prenom']) && empty($user['prenom'])
            || !isset($user['anniversaire']) && empty($user['anniversaire'])
            || !isset($user['tel']) && empty($user['tel']))
            {
                $message = "Votre profil est incomplet";
            }
            else
            {
                $message = "";
            };
            
            $template = "views/user/profile";
            require "views/layout.phtml";
        }
    
    }
    
    /**
     * Méthode permettant de gérer le formulaire de mise à jour des informations du profil
     **/
    public function updateProfile(): void 
    {
        // si formulaire est envoyé et que rien n'est vide
        if(isset($_POST['nom']) && !empty($_POST['nom'])
            && isset($_POST['prenom']) && !empty($_POST['prenom'])
            && isset($_POST['anniversaire']) && !empty($_POST['anniversaire'])
            && isset($_POST['tel']) && !empty($_POST['tel'])
            && isset($_FILES['pdp']) && !empty($_FILES['pdp'])
            && isset($_POST['mail']) && !empty($_POST['mail'])
            && $_POST['token'] == $_SESSION['csrf_token']) 
        {
            //vérification des inputs (regEx)
            $inputs = 
            [
                "nom" => trim($_POST['nom']),
                "prenom" => trim($_POST['prenom']),
                "anniversaire" => trim($_POST['anniversaire']),
                "tel" => trim($_POST['tel']),
                "mail" => trim($_POST['mail'])
            ];
            
            $errors = $this -> verifyInputs($inputs);
            
            $messages = [];
            
            $id_user = $_SESSION['user']['id_user'];
            
            //s'il y a des erreurs, renvoyer à la page
            if (!empty($errors))
            {
                foreach ($errors as $error)
                {
                     array_push($messages, $error);
                }
                
                $message = http_build_query($messages);
                header("location:index.php?action=see-profile&alert=$message");
                exit();
            }
            else
            {
                $nom = $inputs["nom"];
                $prenom = $inputs["prenom"];
                $anniversaire = $inputs["anniversaire"];
                $tel = $inputs["tel"];
                $mail = $inputs["mail"];
            
                if(!empty($_FILES['pdp']['name'])) // si l'utilisateur change sa photo de profil
                {
                    $img = $_FILES['pdp'];
                    
                    $verifImg = $this -> verifyImg($img);
                    
                    if(isset($verifImg['newFileName']))
                    {
                        $tmp_name = $verifImg['tmp_name'];
                        $name = $verifImg['newFileName'];
                        $test_img = move_uploaded_file($tmp_name, $verifImg['uploads_dir'] . '/' . $name);
                        
                        $pdp = $name;
                        
                        $update = $this -> user -> updateUserInfos($nom, $prenom, $anniversaire, $tel, $pdp, $mail, $id_user);
                        
                        if($update) // = true
                        {
                            $old_img = $_SESSION['user']['pdp'];
                            
                            //supprimer l'ancienne image seulement si c'est pas un des avatars de base
                            if($old_img == 'blue.png' 
                                || $old_img == 'pink.png' 
                                || $old_img == 'yellow.png' 
                                || $old_img == 'green.png')
                            {
                                $_SESSION['user']['pdp'] = $pdp;
                                
                                $validation = "Votre profil a bien été modifié";
                                header("location:index.php?action=see-profile&message=$validation");
                                exit();
                            }
                            else
                            {
                                unlink($verifImg['uploads_dir'] . '/' . $old_img);
                                
                                $_SESSION['user']['pdp'] = $pdp;
                                
                                $validation = "Votre profil a bien été modifié";
                                header("location:index.php?action=see-profile&message=$validation");
                                exit();
                            }
                        } 
                        else
                        {
                            $message = "Erreur lors de la mise à jour des informations utilisateur";
                            header("location:index.php?action=see-profile&attention=$message");
                            exit();
                        }
                    }
                    else
                    {
                        $message = $verifImg['error'];;
                        header("location:index.php?action=see-profile&attention=$message");
                        exit();
                    }
                }
                else // si l'utilisateur modifie ses autres infos mais ne change pas sa photo de profil
                {
                    $pdp = $_SESSION['user']['pdp'];
                    
                    $update = $this -> user -> updateUserInfos($nom, $prenom, $anniversaire, $tel, $pdp, $mail, $id_user);
                    
                    $validation = "Votre profil a bien été modifié";
                    header("location:index.php?action=see-profile&message=$validation");
                    exit();
                }
                
            }
        }
        else
        {
            $message = "Une erreur est survenue lors de la mise à jour des informations utilisateur";
            header("location:index.php?action=see-profile&attention=$message");
            exit();
        }
    }
    
    /**
     * Méthode permettant d'envoyer un tableau avec les utilisateurs en fonction de leur pseudo
     **/
    public function findUsers(): void
    {
        if(isset($_GET['pseudo']) && !empty($_GET['pseudo']))
        {
            $pseudo = trim(htmlspecialchars($_GET['pseudo']));
            $users = $this -> user -> getUserByPseudo($pseudo);
            echo json_encode($users);
        }
    }
    
    /**
     * Méthode permettant de récupérer les informations d'autres utilisateurs
     **/
    public function seeOthersProfile(): void
    {
        if(array_key_exists('id',$_GET) && is_numeric($_GET['id']))
        {
                $id_profile = $_GET['id'];
                $id_active_user = $_SESSION['user']['id_user'];
                
                //si l'utilisateur clique sur son propre profil alors il est redirigé vers son propre profil
                if($id_profile == $id_active_user)
                {
                    header("location:index.php?action=see-profile");
                    exit();
                }
                else
                {
                    $user = $this -> user -> getUsersInfoById($id_profile);
                    //sans ça la variable $reviews est pas définie dans la vue
                    $reviews = $this -> review -> getReviewsByUserId($id_profile);
                    //vérification du lien d'amitié
                    $friends = $this -> friend -> verifyFriendship($id_active_user, $id_profile);
                }
        }
        
        $template = "user/others-profile";
        require "views/layout.phtml"; 
    }
    
}