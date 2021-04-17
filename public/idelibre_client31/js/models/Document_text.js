var Document_text = function(id, name){
    this.id = id;
    this.name = name;
    this.annotations =[];
    this.isLoaded;  //NOTLOADED PENDING LOADED
    this.pdfdatas = [];
    this.partLoaded = 0;
};






/**
 * recupere la listes des pdfdatas
 * @returns {Array}
 */
Document_text.prototype.getPdfdatas = function(){
    this.pdfdatas = this.pdfdatas || [];
    return this. pdfdatas;
};


/**
 * verifie si tous les pdfdatas du document sont chargés
 * @returns {Boolean}
 */
Document_text.prototype.checkPdfdatas = function(){
    var res = true;
    _.each(this.getPdfdatas(), function(pdfdata){
        if(pdfdata.isLoaded === false){
            res = false;
        }
    }); 
    return res;
    
};

/**
 * retourne le tableau des annotations s'il existe un tableau vide si non
 * @returns {array of Seance}
 */
Document_text.prototype.getAnnotation = function(){
    this.annotations = this.annotations || [];
    return this.annotations;
};


/**
 * ajoute une annotation à un document
 * @param {annotation} annotation
 * @returns {}
 */
Document_text.prototype.addAnnotation = function(annotation){
    this.annotations.push(annotation);
};