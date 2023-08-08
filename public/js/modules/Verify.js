"use strict";

import Error from "./Error.js";

class Verify {
    constructor(){
        
        this._nom = "";
        this._prenom = "";
        this._anniversaire = "";
        this._tel = "";
        this._mail = "";
        this._mdp = "";
        this._pseudo = "";
        
        this.donnees = [];
        
    }
    
/************************************************************************************/
/* *********************************** GETTERS **************************************/
/************************************************************************************/

    get nom(){
        return this._nom;
    }
    
    get prenom(){
        return this._prenom;
    }
    
    get anniversaire(){
        return this._anniversaire;
    }
    
    get tel(){
        return this._tel;
    }
    
    get mail(){
        return this._mail;
    }
    
    get mdp(){
        return this._mdp;
    }
    
    get pseudo(){
        return this._pseudo;
    }
    
    
    
/************************************************************************************/
/* *********************************** SETTERS **************************************/
/************************************************************************************/   
    
    
    //SETTER NOM
    set nom(nomInput){ // ne doit être pas vide condition = pas moins de 2 caractères, pas de nombres
    
        let nomRegex = new RegExp(/^(?=.*[a-zA-Z0-9àáâäæçèéêëìíîïñòóôöœùúûüÿ])[a-zA-Z0-9àáâäæçèéêëìíîïñòóôöœùúûüÿ]{2,}$/);
        
        if(nomInput.length > 2 && nomRegex.test(nomInput)){
            console.log(nomInput)
            this.donnees.push(nomInput);
        }
        else{
            console.log("Erreur nom");
            let erreur = new Error(".error-nom","Veuillez entrer plus de 2 caractères");
            erreur.showErrors();
        }
    }
    
    // SETTER PRENOM
    set prenom(prenomInput){ // ne doit être pas vide condition = pas moins de 2 caractères, pas de nombres
    
        let prenomRegex = new RegExp(/^(?=.*[a-zA-Z0-9àáâäæçèéêëìíîïñòóôöœùúûüÿ])[a-zA-Z0-9àáâäæçèéêëìíîïñòóôöœùúûüÿ]{2,}$/);
        
        if(prenomInput.length > 2 && prenomRegex.test(prenomInput)){
            console.log(prenomInput)
            this.donnees.push(prenomInput);
        }
        else{
            console.log("Erreur prenom");
            let erreur = new Error(".error-prenom","Veuillez entrer plus de 2 caractères");
            erreur.showErrors();
        }
    }
    
    // SETTER TELEPHONE
    set tel(telInput){ 
    
        let telRegex = new RegExp(/^[0]{1}[0-9]{9}$/); // commence par un 0 et contient 9 autres chiffres
        
        if(telInput.length > 2 && telRegex.test(telInput)){
            console.log(telInput);
            this.donnees.push(telInput);
        }
        else{
            console.log("Erreur téléphone");
            let erreur = new Error(".error-tel","Le numéro de téléphone est invalide");
            erreur.showErrors();
        }
    }
    
    // SETTER MAIL
    set mail(mailInput){ // ne doit pas être vide
        
        let emailRegex = new RegExp(/^[A-Za-z0-9_!#$%&'*+\/=?`{|}~^.-]+@[A-Za-z0-9.-]+$/, "gm"); // @ et .qlqc
        
        
        if(emailRegex.test(mailInput)){
            console.log(mailInput);
            this.donnees.push(mailInput);
        }
        else{
            console.log("Erreur mail");
            //this.mail = "Erreur mail non conforme";
            let erreur = new Error(".error-email","Le format de l'adresse mail est invalide");
            erreur.showErrors();
        }
    }
    
    // SETTER ANNIVERSAIRE + CONDITION SUR L'AGE >= 18
    set anniversaire(annivInput){
        
        let maintenant = new Date();
        let anniv = new Date(annivInput);
        let age = maintenant.getFullYear() - anniv.getFullYear();
        let mois = maintenant.getMonth() - anniv.getMonth();
        
        // une condition pour soustraire 1 an si l'anniversaire n'est pas encore passé
        if (mois < 0 || (mois === 0 && maintenant.getDate() < anniv.getDate())){ // si la différence entre le mois actuel et le mois d'anniversaire est inf à 0 alors l'anniversaire n'est pas encore passé et on soustrait 1 an à son âge, dans la 2ème partie le mois est le même mais on vérifie la date
                age--;
        }
        
        if (age >= 16) {
            console.log(annivInput);
            this.donnees.push(annivInput);
        } 
        else {
            console.log("Erreur age non requis");
            let erreur = new Error(".error-anniversaire","Vous devez avoir plus de 16 ans pour vous inscrire");
            erreur.showErrors();
        }
    }
    
    
    // SETTER MDP
    set mdp(mdpInput){ // ne doit pas être vide
        
        let mdp = new RegExp(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/);
        
        if(mdp.test(mdpInput)){
            // console.log(mdpInput);
            this.donnees.push(mdpInput);
        }
        else{
            console.log("Erreur mdp");
            let erreur = new Error(".error-password","Le mot de passe doit contenir au moins 8 caractères dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial");
            erreur.showErrors();
        }
    }
    
    //SETTER PSEUDO
    set pseudo(pseudoInput){ // ne doit être pas vide condition = pas moins de 2 caractères, pas de nombres
    
        let pseudoRegex = new RegExp(/^(?=.*[a-zA-Z0-9])[a-zA-Z0-9]{2,}$/);
        
        if(pseudoInput.length > 2 && pseudoRegex.test(pseudoInput)){
            console.log(pseudoInput);
            this.donnees.push(pseudoInput);
        }
        else{
            console.log("Erreur pseudo");
            let erreur = new Error(".error-pseudo","Veuillez entrer plus de 2 caractères");
            erreur.showErrors();
        }
    }
    
}

export default Verify;