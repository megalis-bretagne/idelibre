/**
 * 
 * @param {string} id
 * @param {string} name
 * @param {Document_text} document_text
 * @param {string} theme
 * @param {int} rank
 * @param {bool} vote
 * @returns {Projet}
 */
var Projet = function (id, name, document_text, theme, rank, vote, rapporteur) {
    this.id = id;
    this.name = name;
    this.theme = theme;
    this.rank = rank;
    this.vote = vote;
    this.document_text = document_text;
    this.rapporteur = rapporteur;
    this.rapporteurId = rapporteurId;
    this.annexes = [];
    this.annotations = [];


};

/**
 * ajoute une annexe
 * @param {Annexe} annexe
 * @returns {}
 */
Projet.prototype.addAnnexe = function (annexe) {
    this.annexes.push(annexe);
};

/**
 * attribuer un document
 * @param {Document_text} document_text
 * @returns {}
 */
Projet.prototype.setDocument_text = function (document_text) {
    this.document_text = document_text;
};

/**
 * renvoie true si le projet est deja accessible en local
 * @returns {Boolean}
 */
Projet.prototype.isLoaded = function () {
    if (this.document_text) {
        return this.document_text.isLoaded;
    }
    //on retourne true si pas de doc associé au projet    
    else {
        return true;
    }

};



/**
 * retourne le tableau des seances si il existe un tableau vide si non
 * @returns {array} of annexes
 */
Projet.prototype.getAnnexes = function () {
    this.annexes = this.annexes || [];
    return this.annexes;
};


/**
 * cherche l'annexe correspondante à l'id et la renvoie
 * @param {String} annexeId
 * @returns {Annexe}
 */
Projet.prototype.findAnnexe = function (annexeId) {

    var annexe = _.find(this.getAnnexes(), function (annexe) {
        return annexe.annexe_id === annexeId;
    });
    return annexe;
};




Projet.prototype.findAnnotationIndex = function (annotationId) {
       var pos = _.findIndex(this.getAnnotations(), function (annotation) {
        return annotation.id === annotationId;
    });
    return pos;
};




/**
 * nombre de partie de pdf de projet chargée
 * @returns {undefined}
 */
Projet.prototype.countProjetPdfdatasLoaded = function () {
    var num = 0;
    _.each(this.document_text.pdfdatas, function (pdf) {
        if (pdf.isLoaded) {
            num++;
        }
    });
    return num;
};


/**
 * Compte les annotation privées et partagé d'un projet
 * @returns {Projet.prototype.countAnnotations.ProjetAnonym$0}
 */
Projet.prototype.countAnnotations = function () {
    var sharred = 0;
    var private = 0;
    var unread = 0;
    _.each(this.getAnnotations(), function (annotation) {
        if (_.isEmpty(annotation.sharedUserIdList)) {
            private++;
        } else {
            sharred++;
            if (!annotation.isRead) {
                unread++;
            }
        }
    });

    return({private: private, sharred: sharred, unread: unread});
};



Projet.prototype.addAnnotation = function (annotation) {
    if (!this.annotations)
        this.annotations = [];

    var index = _.findIndex(this.annotations, function (annots) {
        return annots.id === annotation.id;
    });

    if (index === -1) {
        //it's a new annotation : 
        this.annotations.push(annotation)
    } else {
        //it's an updated annotation
        this.annotations[index] = annotation;
    }
};




Projet.prototype.deleteAnnotation = function (annotationId) {
    if (!this.annotations)
        this.annotations = [];

    var index = _.findIndex(this.annotations, function (annots) {
        return annots.id === annotationId;
    });
    if (index === -1) {
        return false;
    } else {
        this.annotations.splice(index, 1);
        return true;
    }
};



Projet.prototype.getAnnotations = function () {
    if (!this.annotations)
        this.annotations = [];
    return this.annotations;
};

Projet.prototype.getType = function(){
    return DocType.PROJET;
}




Projet.prototype.deleteAllAnnotations = function(){
    this.annotations =[];
}

