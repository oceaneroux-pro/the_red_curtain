"use strict"
let btnDropDown;
let icon;
let nav;
let toggleBtn;
let toggle;

/* *************************************************************************************
                                      FONCTIONS
************************************************************************************** */

function revealMenu()
{
    nav.classList.toggle("active");
    icon.classList.toggle("fa-xmark");
}

/* *************************************************************************************
                                        DOM
************************************************************************************** */

document.addEventListener('DOMContentLoaded',function(){
    
    btnDropDown = document.getElementById("drop-down-menu");
    
    icon = document.getElementById("menu-icon");
    
    nav = document.querySelector(".hidden-nav");
    
    btnDropDown.addEventListener("click", revealMenu);
    
    toggleBtn = document.getElementById("toggle");
    
    toggle = document.querySelector(".details")
    
    if(toggleBtn && toggle){
        console.log("hello");
        toggleBtn.addEventListener("click", function()
         {
             toggle.classList.toggle("details");
         })
    }
});