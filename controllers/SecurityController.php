<?php

namespace controllers;

class SecurityController
{
    /**
     * Méthode permettant de vérifier si l'utilisateur est connecté
     **/
    public function is_connect(): bool
    {
        if(isset($_SESSION['user']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Méthode permettant de vérifier si l'administrateur est connecté
     **/
    public function isAdmin(): bool
    {
        if(isset($_SESSION['admin']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Méthode permettant de convertir un nombre INT en étoiles
     **/
    public function etoiles(int $note): string 
    {
        $nbEtoilesRemplies = round($note);
            
        $resultat = str_repeat('<i class="fa-solid fa-star"></i>', $nbEtoilesRemplies);
            
        return $resultat;
    }
    
    /**
     * Méthode permettant de générer un token CSRF pour la protection des formulaires à chaque début de session
     **/
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
    
        return $token; // string
    }
    
    /**
     * Méthode permettant de vérifie l'intégrité du fichier envoyé par l'utilisateur 
     * lorsqu'il modifie sa photo de profil
     **/
    public function verifyImg($file): ?array
    {
        // récupère le nom du fichier
        $fileName = $file['name'];
        // récupère son nom temporaire téléchargé
        $fileTmpPath = $file['tmp_name'];
        // récupère sa taille
        $fileSize = $file['size'];
        // renseigne la direction à suivre pour télécharger les images sur l'ide
        $targetDirectory = "public/images/avatars";
        // renseigne les extensions autorisées
        $allowedExtensions = array("jpg", "jpeg", "png");
        // et la taille maximum = 2Mo, taille autorisée par l'ide
        $maxSize = 2 * 1024 * 1024; // 2Mo
        
        // 1- Vérifier l'extension du fichier
        // pathinfo retourne les infos du fichier, on lui donne son nom et précise qu'on cherche à obtenir l'extension seulement
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        //on comprare l'extension récupérée avec les extensions autorisées
        if (!in_array($fileExtension, $allowedExtensions)) {
            return array('error' => "Nous n'acceptons que les formats jpg, jpeg et png");
            exit;
        }
        
        // 2- Vérifier la taille du fichier
        //la taille max configurée par l'ide est 2Mo, si l'utilisateur télécharge une image plus lourde alors PHP ne récupère pas sa taille mais un 0 à la place, cela bloquait le fonctionnement de finfo_file plus bas
        // $uploadMaxSize = ini_get('upload_max_filesize');
        
        if ($fileSize > $maxSize || $fileSize == 0)
        {
            return array('error' => 'La taille du fichier dépasse la limite autorisée (2Mo).');
            exit;
        }
        
        // 3- Vérifier le contenu du fichier
        //cette fonction détermine le type mime du fichier déposé en créant un objet fileinfo
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        
        //finfo_file prend en paramètres l'objet fileinfo créé au dessus et le chemin d'accès au fichier temporaire
        //la fonction renvoie le type mime grace à l'objet fileinfo
        $fileMimeType = finfo_file($fileInfo, $fileTmpPath);
        //fermer la ressource pour ménager le serveur
        finfo_close($fileInfo);
        
        //renseigne les mimes autorisés
        $allowedMimeTypes = array("image/jpeg", "image/png", "image/jpg");
        if (!in_array($fileMimeType, $allowedMimeTypes)) 
        {
            return array('error' => "Le fichier n'est pas une image valide.");
            exit;
        }
        
        // 4- Générer un nouveau nom de fichier unique
        $newFileName = uniqid() . '.' . $fileExtension;
        
        // 5- Retourner le nouveau fichier
        return array(
            'newFileName' => $newFileName,
            'tmp_name' => $fileTmpPath,
            'uploads_dir' => $targetDirectory
        );
    }
    
    
    /**
     * Méthode permettant de vérifier des inputs grâce à des regEx
     **/
    public function verifyInputs(array $inputs): ?array
    {
        $patterns = 
        [
            "nom" => "/^(?=.*[^\d])[\p{L}\d '-]{2,}$/u", // +2 chars, pas de nombres, ni chars spé
            "prenom" => "/^(?=.*[^\d])[\p{L}\d '-]{2,}$/u", // +2 chars, pas de nombres, ni chars spé
            "mail" => "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/", // @ et .
            "tel" => "/^[0]{1}[0-9]{9}$/", //un 0 suivi de 9 chiffres
            "mdp" => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/", // +8 chars, au - 1 majuscule, 1 minuscule, 1 char spé
            "pseudo" => "/^(?=.*[a-zA-Z0-9])[a-zA-Z0-9]{2,}$/", // +2 chars, lettres et nombres ok
            "anniversaire" => "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/" // respecte le format yyyy-mm-dd
        ];
        
        $errorsMsg =
        [
            "nom" => "Le nom doit faire plus de 2 caractères et ne doit pas de contenir de nombres",
            "prenom" => "Le prénom doit faire plus de 2 caractères et ne doit pas de contenir de nombres",
            "mail" => "Le format de l'adresse mail est invalide",
            "tel" => "Le numéro de téléphone est invalide",
            "mdp" => "Le mot de passe doit contenir au moins 8 caractères dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial",
            "pseudo" => "Le pseudo doit faire plus de 2 caractères",
            "anniversaire" => "Vous devez avoir plus de 16 ans pour vous inscrire"
        ];
        
        $errors = [];
        
        foreach ($inputs as $input => $value) 
        {
            if (array_key_exists($input, $patterns)) 
            {
                if (!preg_match($patterns[$input], $value))
                {
                    array_push($errors, $errorsMsg[$input]);
                }
            }
            
            // Vérifier l'âge
            if ($input == "anniversaire")
            {
                $today = new \DateTime();
                $anniv = new \DateTime($value);
                
                $age = $anniv -> diff($today) -> y; //pour récupérer le nombre d'années uniquement
    
                if ($age < 16)
                {
                    array_push($errors, $errorsMsg[$input]);
                }
            }
        }
        
        return $errors;
    }
}