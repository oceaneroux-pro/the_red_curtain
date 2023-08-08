"use strict"

let selected = [];
let id;
let stars;
let btnSubmit;
let verif_id;
let titre;
let date;
let poster;
let type; 
let synopsis;
let formattedDate;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function loadSessionStorage()
{
    selected = window.sessionStorage.getItem("selected");
    
    if(selected == null)
    {
        selected = [];
    }
    else
    {
        selected = JSON.parse(selected); //json --> js
    }
    // console.log(selected)
}

function fetchInfos(){
    // event.preventDefault();
    loadSessionStorage();
    console.log(selected);
    // selected = JSON.stringify(selected);
    
    appendFieldset();
}


function colorizeStars(event){
    
    let targetted = event.target.id;
    console.log("id:", targetted);
    
    //vu que j'utilise querySelectorAll() je récupère une nodelist que je parcours
    for (let i = 0; i < stars.length; i++) {
        let starId = parseInt(stars[i].id); // je récupère les id de toutes les étoiles
        //si les id sont inférireurs ou égaux à l'élément cliqué = les colorer
        if (starId <= targetted) {
            stars[i].classList.add("fas");
        } else {
            stars[i].classList.remove("fas");
        }
    }
}


function verifyIfInputsWereChanged(event)
{
    verif_id = document.getElementById("verif_id");
    titre = document.getElementById("titre");
    date = document.getElementById("date");
    poster = document.getElementById("poster");
    type = document.getElementById("type");
    synopsis = document.getElementById("synopsis");
    
    for (let i = 0; i < selected.length; i++) 
    {
        //dans le tableau la date est au format yyyy-mm-dd, or l'input date est au format dd-mm-yyyy
        if(selected[i].type != "book")
        {
            let dateInSelected = selected[i].date;
            let partie = dateInSelected.split("-");
            formattedDate = partie[2] + "/" + partie[1] + "/" + partie[0];
            
            if(verif_id.value != selected[i].id.toString()
            || titre.value != selected[i].title
            || date.value != formattedDate
            || poster.value != selected[i].poster
            || type.value != selected[i].type
            || synopsis.value != selected[i].synopsis)
            {
                event.preventDefault();
                
                $("#add-review-form").prepend("<p class='red'>Merci de ne pas modifier les informations de l'oeuvre</p>");
                
                $(".targetted-fieldset").empty();
                appendFieldset();
            }
            
        }
        else if(selected[i].type == "book") // les vérifs pour les livres ne sont pas les mêmes
        {
            synopsis = undefined;
            
            if(verif_id.value != selected[i].id.toString()
            || titre.value != selected[i].title
            || poster.value != selected[i].poster
            || type.value != selected[i].type
            || synopsis.value != selected[i].synopsis
            || date.value != selected[i].date.toString())
            {
                event.preventDefault();
                
                $("#add-review-form").prepend("<p class='red'>Merci de ne pas modifier les informations de l'oeuvre</p>");
                
                $(".targetted-fieldset").empty();
                $(".targetted-fieldset").append("<div id='infoCard'><legend class='review-legend'>Informations sur l'oeuvre</legend></div>");
                appendFieldset();
            }
        }
    }
}


function appendFieldset()
{
    for (let i = 0; i < selected.length; i++) 
    {
        console.log(selected[i].id)
        console.log(selected[i].title)
        
        if (selected[i].type == "book"){
            console.log("je suis un livre")
            $("#infoCard").append("<div class='info-card-small'><img src='" + selected[i].poster + "' alt='" + selected[i].title + "'>");
            $("#infoCard").find(".info-card-small").append("<div class='work-infos'><h3 class='review-title'>"+selected[i].title +"</h3><span id='toggle'>Détails</span><div class='details'><p>Auteur: "+selected[i].author+"</p><p>Année de sortie: "+selected[i].date+"</p></div></div></div>");
            
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="verif_id" id="verif_id" value="'+parseInt(selected[i].id)+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="titre" id="titre" value="'+selected[i].title+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="date" id="date" value="'+selected[i].date+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="poster" id="poster" value="'+selected[i].poster+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="type" id="type" value="'+selected[i].type+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="synopsis" id="synopsis" value="'+selected[i].synopsis+'"/>');
        }
        else {
            let date = selected[i].date;
            let partie = date.split("-");
            let formattedDate = partie[2] + "/" + partie[1] + "/" + partie[0];
            
            $("#infoCard").append("<div class='info-card-small'><img src='https://image.tmdb.org/t/p/w500" + selected[i].poster + "' alt='" + selected[i].title + "'>");
            $("#infoCard").find(".info-card-small").append("<div class='work-infos'><h3 class='review-title'>"+selected[i].title +"</h3><span id='toggle'>Détails</span><div class='details'><p>Date de sortie: "+formattedDate+"</p><p>Résumé: "+selected[i].synopsis+"</p></div></div></div>");
            
            // // Ajouter les éléments au formulaire
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="verif_id" id="verif_id" value="'+parseInt(selected[i].id)+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="titre" id="titre" value="'+selected[i].title+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="date" id="date" value="'+formattedDate+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="poster" id="poster" value="'+selected[i].poster+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="type" id="type" value="'+selected[i].type+'"/>');
            $("#add-review-form .targetted-fieldset").append('<input type="hidden" name="synopsis" id="synopsis" value="'+selected[i].synopsis+'"/>');
        }
    }
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    btnSubmit = document.getElementById("bouton");
    
    fetchInfos();
    
    stars = document.querySelectorAll(".fa-star");
    for (let i = 0; i < stars.length; i++) 
    {
        stars[i].addEventListener("click", colorizeStars)
    }
    
     btnSubmit.addEventListener("click", verifyIfInputsWereChanged)
     
});