"use strict"
let stars;
let btnChange;
let btnCancel;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function displayRating() {
    
    $('#note').empty();
    
    let url = window.location.href;

    let action = new URLSearchParams(window.location.search);
    
    let id_review = action.get('id');
    
    let ratingDiv = $("<div id='rating'></div>");
    ratingDiv.append("<input type='radio' class='star' id='star1' name='note' value='1'><label for='star1' class='rating-star'><i class='far fa-star star-1' id='1'></i></label>");
    ratingDiv.append("<input type='radio' class='star' id='star2' name='note' value='2'><label for='star2' class='rating-star'><i class='far fa-star star-2' id='2'></i></label>");
    ratingDiv.append("<input type='radio' class='star' id='star3' name='note' value='3'><label for='star3' class='rating-star'><i class='far fa-star star-3' id='3'></i></label>");
    ratingDiv.append("<input type='radio' class='star' id='star4' name='note' value='4'><label for='star4' class='rating-star'><i class='far fa-star star-4' id='4'></i></label>");
    ratingDiv.append("<input type='radio' class='star' id='star5' name='note' value='5'><label for='star5' class='rating-star'><i class='far fa-star star-5' id='5'></i></label>");
    ratingDiv.append("<a href='index.php?action=modify-review&id="+id_review+"'><i class='fa-solid fa-clock-rotate-left'></i></a>")
    
    $('#note').append(ratingDiv);
}


function waitStars(){
    
    stars = document.querySelectorAll(".fa-star");
    
    console.log(stars)
    
    if (stars.length > 0)
    {
        for (let i = 0; i < stars.length; i++) 
        {
            stars[i].addEventListener("click", colorizeStars)
        }
    }
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

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    btnChange = document.getElementById("change-note");
    btnChange.addEventListener("click", function() {
        displayRating();
        waitStars();
    });
});