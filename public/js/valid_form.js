"use strict";

import Verify from ".//modules/Verify.js";

let form;
let inputsNumber;
let send;
let reset;
let nom;
let prenom;
let anniversaire;
let tel;
let mail;
let mdp;
let pseudo;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function validate(event)
{
    $("span").remove();
    
    nom = document.getElementById("nom");
    prenom = document.getElementById("prenom");
    anniversaire = document.getElementById("anniversaire");
    tel = document.getElementById("tel");
    mail = document.getElementById("mail");
    mdp = document.getElementById("mdp");
    pseudo = document.getElementById("pseudo");
    
    let verify = new Verify();
    
    if(nom){
        nom = nom.value;
        verify.nom = nom;
    }
    
    if(prenom){
        prenom = prenom.value;
        verify.prenom = prenom;
    }
    
    if(anniversaire){
        anniversaire = anniversaire.value;
        verify.anniversaire = anniversaire;
    }
    
    if(tel){
        tel = tel.value;
        verify.tel = tel;
    }
    
    if(mail){
        mail = mail.value;
        verify.mail = mail;
    }
    
    if(mdp){
        mdp = mdp.value;
        verify.mdp = mdp;
    }
    
    if(pseudo){
        pseudo = pseudo.value;
        console.log(pseudo);
        verify.pseudo = pseudo;
    }
    
    console.log(verify.donnees);
    
    if(verify.donnees.length != inputsNumber) 
    {
        event.preventDefault();
    }
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    send = document.getElementById("bouton");
    //soumettre le formulaire
    send.addEventListener("click", validate);
    
    form = document.getElementById("form-sensible");
    inputsNumber = parseInt(form.getAttribute("data-inputs-required"));
    // console.log(inputsNumber)
    
    reset = document.getElementById("annuler");
    
    if(reset)
    {
        reset.addEventListener("click", function(){
            $("span").remove();
        });
    }
});