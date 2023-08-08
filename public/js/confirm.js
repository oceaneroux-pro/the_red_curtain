"use strict"
let form;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function confirmDeleteFriend(event) {
    event.preventDefault();
    
    let confirmation = confirm("Êtes-vous sûr de vouloir supprimer cet ami ?");

    if(confirmation)
    {
        form.submit();
    }
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    form = document.getElementById("delete-friend");
    
    if(form)
    {
        form.addEventListener("submit",confirmDeleteFriend);
    }
    
});