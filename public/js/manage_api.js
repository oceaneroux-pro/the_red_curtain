"use strict"

let btnFind1;
let btnFind2;
let keyup;
let apiKey; // clé api réccupérée depuis The Movie Database, la BDD pour les livres, Open Library, n'a pas de clé
let results;
let selected;
let results_books;
let loader;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function searchMovieDatabase(query) {
    
    apiKey = "a1c61af158a9ce1a97b3d4b8239125e4";
    // je vide la div
    $('#target').empty();
    //je raffraichi aussi la div des livres pour éviter problèmes d'affichages
    $('#target2').empty();
    //je vide le tableau des résultats pour éviter que les nouveaux résultats s'incrémente aux anciens
    results = [];
    
    // j'indique que le paramètre de recherche = ce que l'utilisateur entre dans la barre de recherche
    let research = $("#search-bar").val();
    
    // je transmet le paramètre à la requête API
    let searchUrl = "https://api.themoviedb.org/3/search/multi?api_key="+apiKey+"&type=movie,series"+"&query="+encodeURIComponent(research)+"&sort_by=popularity.desc&limit=10&include_adult=false";

    fetch(searchUrl)
    .then(response => response.json())
    .then(data => {
        console.log(data);
        
        if (data.results && data.results.length > 0) {
          for (let i = 0; i < data.results.length; i++) {
            let result = data.results[i];
            let type = result.media_type;
            let movieId = result.id;
            let director;
            let poster_path = result.poster_path;
        
            let oneResult = {
              id: result.id,
              type: type,
              title: type == "movie" ? result.title : result.name,
              poster: "https://image.tmdb.org/t/p/w500" + poster_path,
              date: type == "movie" ? result.release_date : result.first_air_date,
              synopsis: result.overview
            };
        
            results.push(oneResult);
        
            if (poster_path !== null && poster_path !== undefined) {
              let div = document.createElement('div');
              div.className = 'reviewer result_container';
              div.innerHTML = '<img src="' + oneResult.poster + '" alt="' + oneResult.title + '" id="'+ oneResult.id +'" class="reviewer result-poster">' +
                '<h3 class="reviewer" id="' + oneResult.id + '">' + oneResult.title + '</h3>'
              document.getElementById('target').appendChild(div);
            }
          }
          console.log(results);
        } 
        else
        {
          console.log("Aucun résultat trouvé");
          document.getElementById('target').innerHTML = '<p class="alert"><strong>Oups!</strong> Nous n\'avons rien trouvé. Veuillez réessayer :)</p>';
        }
    })
    .catch(error => console.log(error));
}

function searchBook(query) {
    results_books = [];
    $('#target2').empty();
    $('#target').empty();
    
    //j'appelle un loader car l'api est lent
    $("#loader").show();
    
    let research = $("#search-bar-books").val();
    let searchUrl = "https://openlibrary.org/search.json?title="+encodeURIComponent(research)+"&limit=4";

    $.getJSON(searchUrl, function(response) {
        console.log(response)
        //je cache le loader
        $("#loader").hide();
        if (response.docs && response.docs.length > 0) {
            
            for (let i = 0; i < response.docs.length; i++) 
            {
                // let coverId = result.cover_i ? result.cover_i : undefined;
                let coverId;
                let result = response.docs[i];
                
                console.log(result.cover_i);
                
                if(result.cover_i != undefined)
                {
                    coverId = result.cover_i;
                    // la création de oneResult se fait que si le résultat a un cover_i => sert à éliminer tous les résultats sans id qui n'ont pas d'images (comme les e-book) car l'API de Open Library ne me permet pas de filtrer les résultats dépendemment du type
                    let oneResult = {
                        id: result.cover_i,
                        title: result.title,
                        type: "book",
                        // si author_name existe + qu'il y en a +sieurs = les concaténer avec ",", sinon l'auteur = inconnu
                        author: result.author_name ? result.author_name.join(", ") : "Inconnu",
                        poster:"https://covers.openlibrary.org/b/id/"+coverId+"-M.jpg",
                        date: result.first_publish_year
                    }
                    results_books.push(oneResult)
                }
            }
            console.log(results_books)
            
            for(let i = 0; i < results_books.length; i++)
            {
                $("#target2").append("<div class='result_container'><img src='"+ results_books[i].poster + "' alt='" + results_books[i].title + "' id='"+ results_books[i].id +"' class='reviewer result-poster'>");
                $(".result_container:last-child").append("<h3 class='reviewer' id='"+ results_books[i].id +"'>"+results_books[i].title +"</h3><p>De "+results_books[i].author+"</p>");
            }
            
            console.log(results_books)
        }
        else if (response.status == 500)
        {
            console.log("Erreur serveur 500");
            $('#target2').html('<div class="alert"><strong>Oups!</strong> Nous n\'avons rien trouvé. Veuillez réessayer :)</div>');
        }
        else 
        {
            console.log("Aucun résultat trouvé");
            $('#target2').html('<div class="alert"><strong>Oups!</strong> Nous n\'avons rien trouvé. Veuillez réessayer :)</div>');
        }
  });
}

// au clic sur le bouton reviewer dont on attend la création (recupInfos() est appelée dans searchWork.phtml)
function recupInfos(event){
    // console.log('coucou');
    event.preventDefault(); //j'arrête la redirection
    selected = []; // à chaque fois qu'on appuie sur le bouton reviewer, ça écrase le session-storage
    let result;
    // je récupère l'id du film duquel l'utilisateur a appuyé sur le bouton // event.target = le bouton et comme chaque bouton a été créé dynamiquement, ils ont chacun un id différent
    let targetted = event.target.id;
    
    //je sépare les films des livres
    if(results){
        console.log('Film/Série, id:', targetted);
        result = results.find(element => element.id == targetted);
    }
    else if(results_books){
        console.log('Livre, id:', targetted);
        result = results_books.find(element => element.id == targetted);
    }
    console.log(result)
    selected.push(result);
    console.log(selected)
    // j'enregistre en session storage
    saveSessionStorage();
    //redirection vers le formulaire d'ajout de review avec les données de l'oeuvre en storage
    window.location.href = "index.php?action=add-review";
    // });
}

function saveSessionStorage()
{
    // js --> json 
    selected  = JSON.stringify(selected);
    window.sessionStorage.setItem("selected",selected);
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    loader = document.getElementById("loader");
    
    btnFind1 = document.getElementById("search");
    btnFind1.addEventListener("click", searchMovieDatabase);
    
    btnFind2 = document.getElementById("search2");
    btnFind2.addEventListener("click", searchBook);
    
    
    //attendre la création de la classe "reviewer"  
    $('body').on('click', function(event) {
    if (event.target.classList.contains('reviewer')) {
          recupInfos(event);
        }
    });
    
});