"use strict"
let boutons;
let btn2;
let btn3;
let btn4;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function appendTextareas(event)
{
    event.preventDefault();
    
    switch(event.target){
        case btn2 :
            $("#message").empty()
            $("#message").attr("readonly", "readonly")
            $("#message").append("Cher utilisateur,\n\nNous souhaitons vous informer que votre compte sera supprimé en raison d'une violation de nos règles. Cette décision a été prise pour préserver l'intégrité de notre communauté.\n\nSi vous avez des questions ou des préoccupations, veuillez nous contacter à theredcurtain@contact.com. Nous sommes disponibles pour discuter de cette décision.\n\nCordialement,\nL'équipe the red curtain.")
            console.log("bouton 2")
        break;
        case btn3 :
            $("#message").empty()
            $("#message").attr("readonly", "readonly")
            $("#message").append("Cher utilisateur,\n\nNous tenons à vous informer que votre compte sera supprimé en raison d'une période prolongée d'inactivité.\n\nSi vous souhaitez conserver votre compte, nous vous invitons à vous connecter dans les 10 prochains jours. Cela permettra de confirmer votre intérêt et d'éviter la suppression.\n\nCordialement,\nL'équipe the red curtain.")
            console.log("bouton 3")
        break;
        case btn4 :
            $("#message").empty();
            $("#message").removeAttr("readonly");
            console.log("bouton 4")
        break;
        default :
            $("#message").empty();
            $("#message").append("Veuillez choisir un message")
        break;
    }
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    boutons = document.getElementsByTagName("button");
    
    btn2 = document.getElementById("2");
    btn3 = document.getElementById("3");
    btn4 = document.getElementById("4");
    
    //il y a plusieurs boutons = les parcourir
    for (let i = 0; i < boutons.length; i++) 
    {
        boutons[i].addEventListener("click", appendTextareas);
    }
});