"use strict"
let btnFind;
let searchBar;
let results;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function find() {
  
  $(".comment-list").empty();
  $(".result-count-box").empty();
    
    results = [];
    
    let search = searchBar.value
    
    fetch("index.php?action=filter-comments&data="+search+"")
    
    .then(response => response.json())
    .then(data => 
    {
        console.log(data);
        
        data.forEach(result => 
        {
          let oneComment = 
            {
                id_comment: result.id_comment,
                id: result.id_user,
                picture: result.pdp,
                pseudo: result.pseudo,
                commentaire: result.contenu,
                date: result.date
            };
            
          results.push(oneComment);
        });
    
        console.log(results);
        
        $(".result-count-box").append("<p>"+results.length+" résultats</p>");
        
        if(results.length > 0)
        {
          for(let i = 0; i < results.length; i++)
          {
              let date = results[i].date;
              let partie = date.split(" ");
              
              let ddmmYYYY = partie[0];
              let dateParts = ddmmYYYY.split("-");
              
              let hour = partie[1];
              let hourParts = hour.split(":");
              
              //date au format dd/mm/YYYY H:i
              let formattedDate = dateParts[2] + "/" + dateParts[1] + "/" + dateParts[0];
              let formattedHour = hourParts[0]+":"+hourParts[1];
              
              let commentCard = $("<div class='one-comment-admin'></div>");
              
              commentCard.append("<span class='id-comment'>"+results[i].id_comment+"</span><div class='comment-header'><img src='public/images/avatars/"+results[i].picture+"' alt='Photo de profil"+results[i].pseudo+"' class='profile-picture-small'><h4 class='pseudo'>"+results[i].pseudo+" <span class='id-user'>"+results[i].id+"</span></h4></div>");
              
              commentCard.append("<div class='comment-main-admin'><p>"+results[i].commentaire+"</p><small>Le "+formattedDate+" à "+formattedHour+"</small></div>");
              
              $(commentCard).append("<div class='review-card-button'><a href='index.php?action=delete-comment-admin&id="+results[i].id_comment+"' class='admin-button'>Supprimer le commentaire</a></div>")
              
              $(".comment-list").append(commentCard)
          }
        }
        else
        {
          $("#comments").append("<p>Aucun résultat trouvé</p>")
        }
    })
    
    .catch(error => {
      console.log("Erreur lors de la requête Fetch :", error);
      window.location.href = "index.php?action=manage-comments";
      $("#comments").append("<p>Aucun résultat trouvé</p>")
    });
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    searchBar = document.getElementById("search-bar-admin");
    
    btnFind = document.getElementById("word-search");
    btnFind.addEventListener("click", find);
    
});