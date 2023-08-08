"use strict"
let btnFind;
let searchBar;
let users_results;
let btnSelf;
let btnOthers;
let btnHome;
let loader;
let reviews_results = [];


/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function find() {
  
  $('#target-users').empty();
  
  let value = searchBar.value;
  users_results = []

  console.log($.ajax({
    url: "index.php?action=search-users",
    type: "GET",
    data: { pseudo: value },
    dataType: "json",
    success: function(response) // response = un tableau (getUserByPseudo dans User.php)
    {
      
        for (let i = 0; i < response.length; i++) //parcours le tableau pour isoler les résultats
        {
          
          let result = response[i];
          
          let oneUser = {
            id: result.id_user,
            pseudo: result.pseudo,
            photo: result.pdp
          };
          users_results.push(oneUser);
        }
        //j'ai récupéré un tableau des résultats de la requête
        console.log(users_results);
        
        if(users_results.length > 0)
        {
            $("#results").append("<p>"+users_results.length+" résultats</p>")
        }
        else
        {
            $("#results").append("<p>Aucun résultat. Veuillez réessayer</p>")
        }
        
        //je parcours ce tableau pour afficher les résultats 1 par 1
        for(let i = 0; i < users_results.length; i++)
        {
          $("#target-users").append("<div class='users-result_container'><img src='public/images/avatars/"+ users_results[i].photo + "' alt='" + users_results[i].pseudo + "' class='profile-picture-small'>");
          $(".users-result_container:last-child").append("<h3 class='pseudo'><a href='index.php?action=see-others&id="+users_results[i].id+"'>"+users_results[i].pseudo +"</a></h3></div>");
        }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      console.log("Erreur lors de la requête AJAX :", textStatus, errorThrown);
    }
    }));
}

function getUsersOwnReviews() {
  
  let $feedReviews = $('#feed-reviews');
  // Videz la div "main-feed" en supprimant tous ses enfants directs
  $('#main-feed').children().remove();
  
  reviews_results = [];
  // Réinsérez la div "feed-reviews" dans la div "main-feed"
  $("#main-feed").prepend("<h2 class='section-title'>Mes reviews</h2>");
  $('#main-feed').append($feedReviews);
  $('#feed-reviews').empty();
  $("#loader").show();
  
  console.log($.ajax({
    url: "index.php?action=own-reviews",
    type: "GET",
    dataType: "json",
    success: function(response) // response = un tableau (getUserByPseudo dans User.php)
    {
      
      $("#loader").hide();
      if(response.length === 0)
      {
        console.log("0")
        $("#main-feed").append("<p class='announcement'>Vous n'avez pas publié de reviews pour le moment.</p>");
      }
      else
      {
        for (let i = 0; i < response.length; i++) //parcours le tableau pour isoler les résultats
        {
          
          let result = response[i];
          
          let oneReview = {
            id_user: result.id_user,
            id_review: result.id_review,
            pseudo: result.pseudo,
            pdp: result.pdp,
            contenu: result.contenu,
            note: result.note,
            date: result.date,
            titre : result.titre,
            image: result.image,
            release_date: result.release_date,
            synopsis: result.synopsis,
            cat: result.nom_cat
          };
          reviews_results.push(oneReview);
        }
        //j'ai récupéré un tableau des résultats de la requête
        console.log(reviews_results);
        
        displayReview();
        
      }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      console.log("Erreur lors de la requête AJAX :", textStatus, errorThrown);
    }
    }));
}

function getOthersReviews(){
  
  let $feedReviews = $('#feed-reviews');
  // Vider la div "main-feed" en supprimant tous ses enfants directs
  $('#main-feed').children().remove();
  
  reviews_results = [];
  // Réinsérez la div "feed-reviews" dans la div "main-feed"
  $("#main-feed").prepend("<h2 class='section-title'>Les reviews d'autres utilisateurs</h2>");
  $('#main-feed').append($feedReviews);
  $('#feed-reviews').empty();
  $("#loader").show();
  
  console.log($.ajax({
    url: "index.php?action=others-reviews",
    type: "GET",
    dataType: "json",
    success: function(response) // response = un tableau (getUserByPseudo dans User.php)
    {
      $("#loader").hide();
      if(response.length === 0)
      {
        console.log("0")
        $("#main-feed").append("<p class='announcement'>Nous n'avons pas d'autres comptes à vous proposer.</p>");
      }
      else
      {
        for (let i = 0; i < response.length; i++) //parcours le tableau pour isoler les résultats
        {
          
          let result = response[i];
          
          let oneReview = {
            id_user: result.id_user,
            id_review: result.id_review,
            pseudo: result.pseudo,
            pdp: result.pdp,
            contenu: result.contenu,
            note: result.note,
            date: result.date,
            titre : result.titre,
            image: result.image,
            release_date: result.release_date,
            synopsis: result.synopsis,
            cat: result.nom_cat
          };
          reviews_results.push(oneReview);
        }
        //j'ai récupéré un tableau des résultats de la requête
        console.log(reviews_results);
        
        displayReview();
      }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      console.log("Erreur lors de la requête AJAX :", textStatus, errorThrown);
    }
    }));
}

// Fonction de génération d'étoiles
function etoiles(note) {
  
  let nbEtoilesRemplies = Math.round(note);
  let resultat = "";

  for (let i = 0; i < nbEtoilesRemplies; i++) {
    resultat += '<i class="fa-solid fa-star"></i>';
  }

  return resultat;
}


//Fonction d'affichage
function displayReview(){
  
  for(let i = 0; i < reviews_results.length; i++)
  {
    let date = new Date(reviews_results[i].date);
    let formattedDate = date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'numeric', year: 'numeric' });
    
    let reviewCard = $("<article class='review-card-medium'></article>");
    reviewCard.append("<div class='card-header'>");
    reviewCard.find(".card-header").append("<div class='profile-pic-pseudo'><img src='public/images/avatars/"+reviews_results[i].pdp+"' alt='"+reviews_results[i].pseudo+"' class='profile-picture-small'><h4 class='pseudo'>"+reviews_results[i].pseudo+"</h4></div></div>");
    
    reviewCard.append("<div class='card-main'>");
    reviewCard.find(".card-main").append("<h2 class='review-title'><a href='index.php?action=see-review&id="+reviews_results[i].id_review+"&user="+reviews_results[i].id_user+"'>"+reviews_results[i].titre+"</a></h2>")
    reviewCard.find(".card-main").append("<div class='review-title-poster'><img src='"+reviews_results[i].image+"' alt='"+reviews_results[i].titre+"' class='review-poster'></div>")
    
    reviewCard.find(".card-main").append("<h3 class='subtitle'>Review</h3><span>Note: "+etoiles(reviews_results[i].note)+"</span><p class='review-content'>"+reviews_results[i].contenu+"</p><small>Le "+formattedDate+"</small></div>");
    
    $("#feed-reviews").append(reviewCard);
  }
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */


document.addEventListener('DOMContentLoaded',function(){
    
    loader = document.getElementById("loader");
    
    btnFind = document.getElementById("friend-search");
    btnFind.addEventListener("click", find);
    
    searchBar = document.getElementById("users-search-bar");
    
    btnSelf = document.getElementById("self");
    btnSelf.addEventListener("click", getUsersOwnReviews)
    
    btnOthers = document.getElementById("others");
    btnOthers.addEventListener('click', getOthersReviews);
    
    btnHome = document.getElementById("home");
    btnHome.addEventListener('click', function(event) 
    {
      event.preventDefault();
      window.location.href = 'index.php?action=feed';
    });
    
    //si l'utilisateur clique en dehors de la div des résultats alors celle ci disparait
    //renvoie true si l'élément cliqué != descendant de #target-users
    $(document).click(function(event) {
      if (!$(event.target).closest('#target-users').length) {
        $('#target-users').empty();
      }
    });
    
});