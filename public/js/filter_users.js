"use strict"

let btnFindUsers;
let searchBar;
let select;
let results;
let formattedDate;
let formattedHour;
let today;
let twoYearsAgo;
let users_results;
let loader;

/* *************************************************************************************
                                        DOM
************************************************************************************** */

function findUsers() {
  $('.result-count-box').empty();
  $('#results').empty();
  $("#loader").show();
  let value = searchBar.value;
  users_results = []

  console.log($.ajax({
    url: "index.php?action=search-users",
    type: "GET",
    data: { pseudo: value },
    dataType: "json",
    success: function(response) // response = un tableau (getUserByPseudo dans User.php)
    {
        $("#loader").hide();
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
        
        let longueur = users_results.length;
        
        if(longueur > 0)
        {
            $("#results").append("<p>"+longueur+" résultats</p>")
        }
        else
        {
            $("#results").append("<p>Aucun résultat. Veuillez réessayer</p>")
        }
        
        //je parcours ce tableau pour afficher les résultats 1 par 1
        for(let i = 0; i < users_results.length; i++)
        {
          $("#results").append("<div class='users-result_container'><img src='public/images/avatars/"+ users_results[i].photo + "' alt='" + users_results[i].pseudo + "' class='profile-picture-small'>");
          $(".users-result_container:last-child").append("<h3 class='pseudo'><a href='index.php?action=manage-users-profile&id="+users_results[i].id+"'>"+users_results[i].pseudo+"</a></h3></div>");
        }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      console.log("Erreur lors de la requête AJAX :", textStatus, errorThrown);
    }
    }));
}


function getRecentLastLogin()
{
    $('.result-count-box').empty();
    $(".users-display").empty();
    
    results = [];
    
    fetch("index.php?action=recent-login")
    
    .then(response => response.json())
    .then(data => 
    {
        data.forEach(result => 
        {
          let oneUser = 
            {
                id: result.id_user,
                pseudo: result.pseudo,
                photo: result.pdp,
                login: result.last_login
            };
            
          results.push(oneUser);
        });
    
        console.log(results);
        
        $(".result-count-box").append("<h3>Utilisateurs triés par: date de connexion antérieure à 2 ans</h3>")
        
        displayUserCard("green");
    })
    
    .catch(error => {
    console.log("Erreur lors de la requête Fetch :", error);
    });
}



function getOldestLastLogin()
{
    $('.result-count-box').empty();
    $(".users-display").empty();
    
    results = [];
    
    fetch("index.php?action=old-login")
    
    .then(response => response.json())
    .then(data => 
    {
        data.forEach(result => 
        {
          let oneUser = 
            {
                id: result.id_user,
                pseudo: result.pseudo,
                photo: result.pdp,
                login: result.last_login
            };
          results.push(oneUser);
        });
    
        console.log(results);
        
        $(".result-count-box").append("<h3>Utilisateurs triés par: date de connexion supérieure à 2 ans</h3>")
        
        
        if(results.length == 0)
        {
            $(".users-display").append("<p>Aucun résultat</p>")
        }
        else
        {
            for(let i = 0; i < results.length; i++)
            {
                if(results[i].login == undefined)
                {
                    results[i].login = "date trop ancienne"
                    
                    let userCard = $("<article class='user-card'></article>");
            
                    $(userCard).append("<div class='users-result_container'>")
                    userCard.find(".users-result_container").append("<img src='public/images/avatars/"+ results[i].photo + "' alt='" + results[i].pseudo + "' class='profile-picture-small'> <h3 class='pseudo'><a href='index.php?action=manage-users-profile&id="+results[i].id+"'>"+results[i].pseudo+"</a></h3></div>");
                    
                    $(userCard).append("<div class='user-main'>")
                    userCard.find(".user-main").append("<p>Dernière connexion le "+results[i].login+"</p></div></div>");
                    
                    $(".users-display").append(userCard)
                }
                else
                {
                    displayUserCard("red");
                }
            }
        }
    })
    
    .catch(error => {
    console.log("Erreur lors de la requête Fetch :", error);
    });
}

function getUsersWithNoPosts()
{
    $('.result-count-box').empty();
    $(".users-display").empty();
    
    results = [];
    
    fetch("index.php?action=no-posts")
    
    .then(response => response.json())
    .then(data => 
    {
        data.forEach(result => 
        {
          let oneUser = 
            {
                id: result.id_user,
                pseudo: result.pseudo,
                photo: result.pdp,
                login: result.last_login
            };
          results.push(oneUser);
        });
    
        console.log(results);
        
        $(".result-count-box").append("<h3>Utilisateurs triés par: 0 posts</h3>")
        
        for(let i = 0; i < results.length; i++)
        {
            twoYearsAgo.setFullYear(today.getFullYear() - 2);
            
            if(results[i].login == undefined)
            {
                results[i].login = "date trop ancienne"
                
                let userCard = $("<article class='user-card'></article>");;
            
            $(userCard).append("<div class='users-result_container'>")
            userCard.find(".users-result_container").append("<img src='public/images/avatars/"+ results[i].photo + "' alt='" + results[i].pseudo + "' class='profile-picture-small'> <h3 class='pseudo'><a href='index.php?action=manage-users-profile&id="+results[i].id+"'>"+results[i].pseudo+"</a></h3></div>");
            
            $(userCard).append("<div class='user-main'>")
            userCard.find(".user-main").append("<p>Dernière connexion le "+results[i].login+"</p></div></div>");
            
            $(".users-display").append(userCard)
                
                
                if(new Date(results[i].login) > twoYearsAgo)
                {
                    let date = results[i].login;
                    let partie = date.split(" ");
                    
                    let ddmmYYYY = partie[0];
                    let dateParts = ddmmYYYY.split("-");
                    
                    let hour = partie[1];
                    let hourParts = hour.split(":");
                    
                    //date au format dd/mm/YYYY H:i
                    formattedDate = dateParts[2] + "/" + dateParts[1] + "/" + dateParts[0];
                    formattedHour = hourParts[0]+":"+hourParts[1];
            
                    let userCard = $("<article class='user-card'></article>");;
                
                    $(userCard).append("<div class='users-result_container'>")
                    userCard.find(".users-result_container").append("<img src='public/images/avatars/"+ results[i].photo + "' alt='" + results[i].pseudo + "' class='profile-picture-small'> <h3 class='pseudo'><a href='index.php?action=manage-users-profile&id="+results[i].id+"'>"+results[i].pseudo+"</a></h3></div>");
                    
                    $(userCard).append("<div class='user-main'>")
                    userCard.find(".user-main").append("<p class='red-admin'>Dernière connexion le "+formattedDate+" à "+formattedHour+"</p></div></div>");
                    
                    $(".users-display").append(userCard)
                }
            }
            else if(results[i].login != undefined || new Date(results[i].login) < twoYearsAgo)
            {
                let date = results[i].login;
                let partie = date.split(" ");
                
                let ddmmYYYY = partie[0];
                let dateParts = ddmmYYYY.split("-");
                
                let hour = partie[1];
                let hourParts = hour.split(":");
                
                //date au format dd/mm/YYYY H:i
                formattedDate = dateParts[2] + "/" + dateParts[1] + "/" + dateParts[0];
                formattedHour = hourParts[0]+":"+hourParts[1];
        
                let userCard = $("<article class='user-card'></article>");;
            
                $(userCard).append("<div class='users-result_container'>")
                userCard.find(".users-result_container").append("<img src='public/images/avatars/"+ results[i].photo + "' alt='" + results[i].pseudo + "' class='profile-picture-small'> <h3 class='pseudo'><a href='index.php?action=manage-users-profile&id="+results[i].id+"'>"+results[i].pseudo+"</a></h3></div>");
                
                $(userCard).append("<div class='user-main'>")
                userCard.find(".user-main").append("<p class='green-admin'>Dernière connexion le "+formattedDate+" à "+formattedHour+"</p></div></div>");
                
                $(".users-display").append(userCard)
            }
        }
    })
    
    .catch(error => {
    console.log("Erreur lors de la requête Fetch :", error);
    });
}

function manageSelected(){
    let selected = select.value
    
    switch(selected)
    {
        case "recent" : 
            getRecentLastLogin()
        break;
        case "old" :
            getOldestLastLogin()
        break;
        case "no-posts" :
            getUsersWithNoPosts()
        break;
        default :
            window.location.href = "index.php?action=manage-users";
        break;
    }
    
}

function displayUserCard(color)
{
    for(let i = 0; i < results.length; i++)
    {
            let date = results[i].login;
            let partie = date.split(" ");
            
            let ddmmYYYY = partie[0];
            let dateParts = ddmmYYYY.split("-");
            
            let hour = partie[1];
            let hourParts = hour.split(":");
            
            //date au format dd/mm/YYYY H:i
            formattedDate = dateParts[2] + "/" + dateParts[1] + "/" + dateParts[0];
            formattedHour = hourParts[0]+":"+hourParts[1];
    
        let userCard = $("<article class='user-card'></article>");;
    
        $(userCard).append("<div class='users-result_container'>")
        userCard.find(".users-result_container").append("<img src='public/images/avatars/"+ results[i].photo + "' alt='" + results[i].pseudo + "' class='profile-picture-small'> <h3 class='pseudo'><a href='index.php?action=manage-users-profile&id="+results[i].id+"'>"+results[i].pseudo+"</a></h3></div>");
        
        $(userCard).append("<div class='user-main'>")
        userCard.find(".user-main").append("<p class='"+color+"-admin'>Dernière connexion le "+formattedDate+" à "+formattedHour+"</p></div></div>");
        
        $(".users-display").append(userCard)
    }
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    loader = document.getElementById("loader");
    
    btnFindUsers = document.getElementById("friend-search");
    btnFindUsers.addEventListener("click", findUsers);
    
    searchBar = document.getElementById("users-search-bar");
    
    select = document.getElementById("filter-select");
    select.addEventListener("change", manageSelected)
    
    today = new Date();
    twoYearsAgo = new Date();
    
});