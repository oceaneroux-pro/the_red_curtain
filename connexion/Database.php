<?php
namespace connexion;

class Database
{
    // attributs
    private const SERVER = "";
    private const DB = "";
    private const USER = "";
    private const MDP = "";
    private $connexion;
    
    function getConnexion(): ?\PDO
    {
        try
        { // lance la connexion à la BDD 
            $this -> connexion = new \PDO("mysql:host=".self::SERVER.";dbname=".self::DB.";charset=utf8",self::USER,self::MDP);
        }
        // s'il trouve des erreurs 
        catch(Exception $message)
        {
            die('Message erreur connexion BDD'.$message->getMessage());
        }
        
        return $this -> connexion;
    }
}