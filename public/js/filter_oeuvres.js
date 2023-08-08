"use strict"
let select;
let results;
let currentPage = 1;
let itemsPerPage = 10;
let total = 0;
let pages = 0;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function getSeries()
{
    $("#oeuvres").empty();
    
    results = [];
    
    fetch("index.php?action=get-series")
    
    .then(response => response.json())
    .then(data => 
    {
        
        total = data.total;
        let oeuvres = data.oeuvres;
        
        oeuvres.forEach(result => 
        {
          let oneOeuvre = 
            {
                id: result.id_oeuvre,
                titre: result.titre,
                image: result.image,
                date: result.release_date,
                synopsis: result.synopsis,
                cat: result.nom_cat
            };
            
          results.push(oneOeuvre);
          
        });
    
        console.log(results);
        
        pages = Math.ceil(total / itemsPerPage);
        
            createPagination();
            displayResults();
    })
    
    .catch(error => {
    console.log("Erreur lors de la requête Fetch :", error);
    });
}


function getFilms()
{
    $("#oeuvres").empty();
    
    results = [];
    
    fetch("index.php?action=get-films")
    
    .then(response => response.json())
    .then(data => 
    {
        total = data.total;
        let oeuvres = data.oeuvres;
        
        oeuvres.forEach(result => 
        {
          let oneOeuvre = 
            {
                id: result.id_oeuvre,
                titre: result.titre,
                image: result.image,
                date: result.release_date,
                synopsis: result.synopsis,
                cat: result.nom_cat
            };
            
          results.push(oneOeuvre);
          
        });
    
        console.log(results);
        
        pages = Math.ceil(total / itemsPerPage);
        
            createPagination();
            displayResults();
    })
    
    .catch(error => {
    console.log("Erreur lors de la requête Fetch :", error);
    });
}

function getLivres()
{
    $("#oeuvres").empty();
    
    results = [];
    
    fetch("index.php?action=get-livres")
    
    .then(response => response.json())
    .then(data => 
    {
        total = data.total;
        let oeuvres = data.oeuvres;
        
        oeuvres.forEach(result => 
        {
          let oneOeuvre = 
            {
                id: result.id_oeuvre,
                titre: result.titre,
                image: result.image,
                date: result.release_date,
                synopsis: result.synopsis,
                cat: result.nom_cat
            };
            
          results.push(oneOeuvre);
          
        });
    
        console.log(results);
        
        pages = Math.ceil(total / itemsPerPage);
        
            createPagination();
            displayResults();
    })
    
    .catch(error => {
    console.log("Erreur lors de la requête Fetch :", error);
    });
}


function displayResults()
{
    $("#oeuvres").empty();
    
    //index du tableau à 0 et currentPage initialisée à 1
    let start = (currentPage -1) * itemsPerPage;
    //reprend la position de l'index et indique le nombre d'items à récupérer
    // page1 -> 0+5 = 10 page2 -> 5+5 = 10
    let end = start + itemsPerPage;
    
    for(let i = start; i < results.length && i < end; i++)
    {
        let oeuvreCard = $("<article class='work-card'></article>");
        
        oeuvreCard.append("<span class='id-oeuvre'>"+results[i].id+"</span>");
        
        oeuvreCard.append("<div class='flex'><img src='"+ results[i].image + "' alt='" + results[i].titre + "' class='vignette'></div>")
    
        if(results[i].cat == "Livre")
        {
            oeuvreCard.append("<div class='flex'><h2 class='subtitle-admin'>"+results[i].titre+"</h2><small>Sorti en "+results[i].date+"</small></div>")
        }
        else
        {
            oeuvreCard.append("<div class='flex'><h2 class='subtitle-admin'>"+results[i].titre+"</h2><small>Sorti le "+results[i].date+"</small></div>")
        }
        
        $("#oeuvres").append(oeuvreCard)
    }
}


function manageSelected(){
    let selected = select.value
    
    switch(selected)
    {
        case "serie" : 
            getSeries()
        break;
        case "film" :
            getFilms()
        break;
        case "livre" :
            getLivres()
        break;
        default :
            window.location.href = "index.php?action=see-catalogue";
        break;
    }
}

function createPagination() {
    
    $(".pagination").empty();
    
    for (let i = 1; i <= pages; i++) 
    {
        let button = $("<button class='check admin-button'></button>").append(i);
        
        button.on('click', function(){
            currentPage = i;
            displayResults();
        });
        
        $(".pagination").append(button);
    }

}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    select = document.getElementById("filter-select");
    select.addEventListener("change", manageSelected)
    
});