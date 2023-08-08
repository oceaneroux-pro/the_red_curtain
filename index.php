<?php
session_start();

// les namespace 
use connexion\Database;
use controllers\UserController;
use controllers\ReviewController;
use controllers\SecurityController;
use controllers\NotificationController;
use controllers\CommentController;
use controllers\FriendController;
use controllers\AdminController;
use controllers\OeuvreController;

//autoload
function chargerClasse($classe)
{
    $classe = str_replace('\\','/',$classe);      
    require $classe.'.php'; 
}

spl_autoload_register('chargerClasse'); //fin Autoload

// appel aux controllers 
$userController = new UserController();
$reviewController = new ReviewController();
$securityController = new SecurityController();
$notificationController = new NotificationController();
$commentController = new CommentController();
$friendController = new FriendController();
$adminController = new AdminController();
$oeuvreController = new OeuvreController();

//je génère un token s'il n'existe pas déjà
if (!isset($_SESSION['csrf_token'])) {
    $securityController -> generateToken();
}

//je compte les notifs sur tout le site
if($securityController -> is_connect())
{
    $notificationController -> countNotifs();
}

//router
if($securityController -> is_connect()) // si l'utilisateur est connecté
{
    if(array_key_exists("action",$_GET))
    {
        switch($_GET['action'])
        {
            case "logout" :
                $userController-> deconnexion(); 
                break;
            case "feed" : 
                $friendController-> displayFriendsReviews();
                break;
            case "own-reviews" : 
                $reviewController-> displayUsersOwnReviewsAjax(); 
                break;
            case "others-reviews" : 
                $reviewController-> displayOthersReviews(); 
                break;
            case "search" :
                $reviewController-> showSearchBoxes();
                break;
            case "add-review" :
                $reviewController-> addReview();
                break;
            case "search-users" : 
                 $userController-> findUsers();
                break;
            case "see-profile" : 
                $userController-> displayUserInfos();
                break;
            case "update-profile" : 
                $userController-> updateProfile();
                break;
            case "see-others" : 
                $userController-> seeOthersProfile();
                break;
            case "add-friend" : 
                $notificationController -> addFriend();
                break;
            case "see-notifs" : 
                $notificationController -> displayNotifs();
                break;
            case "accept-friend" : 
                $notificationController -> acceptFriend();
                $friendController -> insertFriendship();
                break;
            case "delete-notif" : 
                $notificationController -> refuseFriend();
                break;
            case "see-review" : 
                $notificationController -> notifyComment();
                $reviewController-> displayOneReview();
                break;
            case "modify-review" : 
                $reviewController -> modifyReview();
                break;
            case "delete-review" : 
                $reviewController -> confirmDeleteReview();
                break;
            case "delete-friend" : 
                $friendController -> destroyFriendship();
                break;
            default :
                header("location:index.php?action=feed");
                exit;
                break;
        }
    }
    else
    {
        header("location:index.php?action=feed");
        exit;
    }
}
else if($securityController -> isAdmin()) // sinon si c'est l'admin qui est connecté
{
    if(array_key_exists("action",$_GET))
    {
        switch($_GET['action'])
        { 
            case "admin" : 
                $adminController -> connexionAdmin();
                break;
            case "admin-logout" : 
                $adminController -> deconnexionAdmin();
                break;
            case "admin-panel" : 
                $oeuvreController -> displayOeuvres();
                break;
            case "manage-users" : 
                $adminController -> displayAllUsers();
                break;
            case "search-users" : 
                 $userController-> findUsers();
                break;
            case "recent-login" :
                $adminController -> findUsersByRecentLogin();
                break;
            case "old-login" :
                $adminController -> findUsersByOldLogin();
                break;
            case "no-posts" :
                $adminController -> findUsersWithNoPosts();
                break;
            case "manage-users-profile" :
                $adminController -> manageProfile();
                break;
            case "manage-posts" :
                $adminController -> managePosts();
                break;
            case "search-posts" :
                $adminController -> filterReviewsByDate();
                break;
            case "get-series" :
                $oeuvreController -> displaySeries();
                break;
            case "get-films" :
                $oeuvreController -> displayFilms();
                break;
            case "get-livres" :
                $oeuvreController -> displayLivres();
                break;
            case "manage-review" :
                $reviewController -> manageOneReview();
                break;    
            case "send-admin-notif" :
                $notificationController -> sendAdminNotif();
                break;     
            case "delete-post-admin" :
                $notificationController -> sendDeletedNotif();
                $adminController -> confirmDeletePostAdmin();
                break; 
            case "delete-comment-admin" :
                $notificationController -> sendDeletedNotif();
                $adminController -> confirmDeleteCommentAdmin();
                break;
            case "delete-user" :
                $adminController -> confirmDeleteUser();
                break;
            case "manage-comments" :
                $commentController -> getEveryComment();
                break;
            case "filter-comments" :
                $commentController -> filterComments();
                break;
            default :
                header("location:index.php?action=admin-panel");
                exit;
                break;
        }
    }
    else
    {
        header("location:index.php?action=admin-panel");
        exit;
    }
}
else // si personne n'est connecté
{
    if(array_key_exists("action",$_GET))
    {
        switch($_GET['action'])
        {
            case "create-account" :
                $userController-> createAccount();
                break;
            case "login" :
                $userController-> connexion(); 
                break;
            case "admin" :
                $adminController -> connexionAdmin();
                break;
            default :
                header("location:index.php?action=create-account");
                exit;
                break;
        }
    }
    else
    {
        header("location:index.php?action=create-account");
        exit;
    }
    
}