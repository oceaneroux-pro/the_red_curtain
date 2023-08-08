"use strict";

class Error{
    constructor(champ,errText)
    {
        this._champ = champ;
        this._errText = errText;
    }
    
    showErrors(){
        
        let span;
        // span.remove();
        span = document.createElement("span");
        span.textContent = this._errText;
        span.classList.add("form-error");
        // span.classList.remove("form-error");
        
        document.querySelector(this._champ).append(span);
        
    }
}

export default Error;