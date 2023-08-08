"use strict"
let btnFindPosts;
let date;
let dateInput;
let results;
let btnFindUsers;
let searchBar;
let users_results;
let loader;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function findPosts() {
  
  $('.result-count-box').empty();
  $('#feed-reviews-admin').empty();
  $("#loader").show();
  date = dateInput.value;
  console.log(date)
  results = []

  console.log($.ajax({
    url: "index.php?action=search-posts",
    type: "GET",
    data: { date: date },
    dataType: "json",
    success: function(response) // response = un tableau (getUserByPseudo dans User.php)
    {
        $("#loader").hide();
        for (let i = 0; i < response.length; i++) //parcours le tableau pour isoler les résultats
        {
          let result = response[i];
          
          let oneReview = {
            id_review: result.id_review,
            pseudo: result.pseudo,
            photo: result.pdp,
            id_user: result.id_user,
            titre: result.titre,
            image: result.image,
            id_oeuvre: result.id_oeuvre,
            contenu: result.contenu,
            date: result.date,
            pdp: result.pdp
          };
          results.push(oneReview);
        }
        //j'ai récupéré un tableau des résultats de la requête
        console.log(results);
        
        let longueur = results.length
        
        if(longueur == 0)
        {
            $(".result-count-box").append("<p>Aucun résultat</p>");
        }
        else
        {
            if(longueur == 1)
            {
                $(".result-count-box").append("<p>"+longueur+" résultat</p>");
            }
            else
            {
                $(".result-count-box").append("<p>"+longueur+" résultats</p>");
            }
            
            for(let i = 0; i < results.length; i++)
            {
                let date = new Date(results[i].date);
                let formattedDate = date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'numeric', year: 'numeric' });
                
                let $reviewCard = $("<article class='review-card-admin'></article>");
                
                $reviewCard.append("<span class='id-post'>#"+results[i].id_review+"</span>");
                
                $reviewCard.append("<div class='card-header'>");
                $reviewCard.find(".card-header").append("<div class='profile-pic-pseudo'><img src='public/images/avatars/"+results[i].pdp+"' alt='"+results[i].pseudo+"' class='profile-picture-small'><h4 class='pseudo'>"+results[i].pseudo+" <span class='id-user'>#"+results[i].id_user+"</span></h4></div></div>");
                
                $reviewCard.append("<div class='review-card-button'><a href='index.php?action=manage-review&id="+results[i].id_review+"&user="+results[i].id_user+"' class='fake-button'>Consulter</a></div></div>");
                
                $reviewCard.append("<div class='card-main'><div class='review-title-poster'><img src="+results[i].image+" alt="+results[i].titre+" class='review-poster-admin'></div><h4 class='subtitle-admin'>Contenu</h4><p class='review-content-admin'>"+results[i].contenu+"</p><small>Le "+formattedDate+"</small></div>");
                
                $("#feed-reviews-admin").append($reviewCard);
            }
        }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      console.log("Erreur lors de la requête AJAX :", textStatus, errorThrown);
    }
    }));
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    loader = document.getElementById("loader");
    
    dateInput = document.getElementById("datetime");
    
    btnFindPosts = document.getElementById("date-search");
    btnFindPosts.addEventListener("click", findPosts);
    
});