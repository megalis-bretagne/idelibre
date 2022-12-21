/**
 * @constructor
 * @param {uuid} id
 * @param {string} name
 * @param {number} rev
 * @param {data} dateSeance
 * @returns {Seance}
 */
var Seance = function (id, name, rev, dateSeance) {

    this.id = id;
    this.name = name;
    this.rev = rev;
    this.date = dateSeance;


    /*
     * si la seance a été modifiée
     * @type = boolean
     */
    this.isModified = null;


    /**
     * @type = array of User
     */
    this.users = [];

    /**
     *  @type = Convocation
     */
    this.convocation = null;

    this.invitation = null;

    /**
     * @type array of Projet
     */
    this.projets = [];


    /**
     * @type array of Otherdoc
     */
    this.otherdocs = [];
};


Seance.PRESENT = "present";
Seance.ABSENT = "absent";
Seance.UNDEFINED = "undefined";

/**
 * Nombre de pdf datas pour la seance
 * @returns {undefined}
 */
Seance.prototype.countConvocationPdfdatas = function () {
    return this.convocation.document_text.pdfdatas.length;
};


/**
 * nombre de partie de pdf de convocation chargée
 * @returns {undefined}
 */
Seance.prototype.countConvocationPdfdatasLoaded = function () {
    var num = 0;
    _.each(this.convocation.document_text.pdfdatas, function (pdf) {
        if (pdf.isLoaded) {
            num++;
        }
    });
    return num;
};




/**
 * retourne le tableau des seances si il existe un tableau vide si non
 * @returns {array of Seance}
 */
Seance.prototype.getProjets = function () {
    this.projets = this.projets || [];
    return this.projets;
};


/**
 * retourne le tableau d'id des projets si il existe un tableau vide si non
 * @returns {array of Seance}
 */
Seance.prototype.listProjetIds = function () {
    var projetIds = []
    this.projets = this.projets || [];
    _.each(this.projets, function (projet) {
        projetIds.push(projet.id);
    });

    return projetIds;
};

Seance.prototype.stringProjetIds = function () {
    var projetIds = "";
    this.projets = this.projets || [];
    _.each(this.projets, function (projet) {
        projetIds += "'" + projet.id + "' , "
    });

    //on retourne le text moins la derniere virgule
    return projetIds.substring(0, projetIds.length - 2);
};


/**
 * retourne le tableau des seances si il existe un tableau vide si non
 * @returns {array of Seance}
 */
Seance.prototype.getOtherdocs = function () {
    this.otherdocs = this.otherdocs || [];
    return this.otherdocs;
};


/**
 * retourne le tableau d'id des autres doucments si il existe un tableau vide si non
 * @returns {array of Seance}
 */
Seance.prototype.listOtherdocIds = function () {
    var otherdocIds = []
    this.otherdocs = this.otherdocs || [];
    _.each(this.otherdocs, function (otherdoc) {
        otherdocIds.push(otherdoc.id);
    });

    return otherdocIds;
};

Seance.prototype.stringOtherdocsIds = function () {
    var otherdocIds = "";
    this.otherdocs = this.otherdocs || [];
    _.each(this.otherdocs, function (otherdoc) {
        otherdocIds += "'" + otherdoc.id + "' , "
    });

    //on retourne le text moins la derniere virgule
    return otherdocIds.substring(0, otherdocIds.length - 2);
};
/**
 *
 * @param {user} user
 * @returns {}
 */
Seance.prototype.addUser = function (user) {
    this.users.push(user);
};

/**
 *
 * @param {Projet} projet
 * @returns {}
 */
Seance.prototype.addProjet = function (projet) {
    this.projets.push(projet);
};


/**
 *
 * @param {Otherdoc} otherdoc
 * @returns {}
 */
Seance.prototype.addOtherdoc = function (otherdoc) {
    this.otherdocs.push(otherdoc);
};


/**
 * attribuer une convocation
 * @param {Convocation} convocation
 * @returns {}
 */
Seance.prototype.setConvocation = function (convocation) {
    this.convocation = convocation;
};


/**
 * return true si la convocation a deja été lue.
 * @returns {boolean}
 */
Seance.prototype.isUnreadConvocation = function () {
    if (this.convocation) {
        return this.convocation.isRead;
    }
    else if(this.invitation){
        return this.invitation.isRead
    }
    // si il n'y a pas de convocation alors elle est concidérée comme lue
    else {
        return true;
    }
};


/**
 * nombre total de porjet de la seance
 * @returns {number}
 */
Seance.prototype.countProjets = function () {
    return this.getProjets().length;
};


/**
 * nombre total de otherdoc de la seance
 * @returns {number}
 */
Seance.prototype.countOtherdocs = function () {
    return this.getOtherdocs().length;
};


/**
 * nombre de projets chargés
 * @returns {Number}
 */
Seance.prototype.countLoadedProjets = function () {
    var loadedProjets = 0;
    for (var i = 0, ln = this.projets.length; i < ln; i++) {
        if (this.projets[i].isLoaded()) {
            loadedProjets++;
        }
    }
    return loadedProjets;

};

/**
 * nombre de otherdocs chargés
 * @returns {Number}
 */
Seance.prototype.countLoadedOtherdocs = function () {
    var loadedOtherdocs = 0;
    for (var i = 0, ln = this.otherdocs.length; i < ln; i++) {
        if (this.otherdocs[i].isLoaded()) {
            loadedOtherdocs++;
        }
    }
    return loadedOtherdocs;

};
Seance.prototype.isLoadedConvocationDocument = function () {
    if (this.convocation && this.convocation.document_text) {
        return this.convocation.document_text.isLoaded;
    }else if (this.invitation && this.invitation.document_text) {
        return this.invitation.document_text.isLoaded;
    }
    return 0;
};


Seance.prototype.isLoadedInvitation = function () {
    console.log("ISLOADEDINVITATION");
    if (this.invitation && this.invitation.document_text) {
        console.log(this.invitation.document_text);
        console.log(this.invitation.document_text.isLoaded);
        return this.invitation.document_text.isLoaded;
    }
    return 0;
};


/**
 * renvoie la liste des document_text.id de tous les projets de la seance
 * @returns {array of documentId}
 */
Seance.prototype.getProjetDocumentsId = function () {
    // array de'id des documents
    var documentsId = [];
    _.each(this.getProjets(), function (projet) {
        documentsId.push(projet.document_text.id);
    });
    return (documentsId);
};


/**
 * renvoie la liste des document_text.id de tous les autres doc de la seance
 * @returns {array of documentId}
 */
Seance.prototype.getOtherdocDocumentsId = function () {
    // array de'id des documents
    var documentsId = [];
    _.each(this.getOtherdocs(), function (otherdoc) {
        documentsId.push(otherdoc.document_text.id);
    });
    return (documentsId);
};



/**
 * Retourne l'id du document de la convocation de la seance
 * @returns {uuid}
 */
Seance.prototype.getConvocationDocumentId = function () {
    return this.convocation.document_text.id;
};




/**
 * cherche le projet correspondante à l'id et la renvoie
 * @param {String} seanceId
 * @returns {Seance}
 */
Seance.prototype.findProjet = function (projetId) {

    var projet = _.find(this.getProjets(), function (projet) {
        return projet.id === projetId;
    });
    return projet;
};


/**
 * cherche le projet correspondante à l'id et la renvoie
 * @param {String} seanceId
 * @returns {Seance}
 */
Seance.prototype.findOtherdoc = function (otherdocId) {

    var otherdoc = _.find(this.getOtherdocs(), function (otherdoc) {
        return otherdoc.id === otherdocId;
    });
    return otherdoc;
};



/**
 * compte le nombre d'annotation non lue d'une seance
 * @returns {Number}
 */
Seance.prototype.isUnreadAnnotation = function () {
    var res = 0;
    _.each(this.getProjets(), function (projet) {
        _.each(projet.getAnnotations(), function(annotation){
            if (!annotation.isRead){
                res++;
            }
        });


    });
    return res;

};




Seance.prototype.getSharedUsers = function (user_id) {
    if(!this.users)
        return null;

    var index = _.findIndex(this.users, function (user) {
        return user.id == user_id;
    });
    if (index > -1) {
        this.users.splice(index, 1);
    }
    this.users.forEach(function (user) {
        user.isShared = false;
    });

    console.log(this.users);
    return this.users;
}


Seance.prototype.getPresentStatus = function(){
    if(this.presentStatus)
        return this.presentStatus;
    return Seance.UNDEFINED;

};



Seance.prototype.setPresentStatus = function(isPresent){
    this.presentStatus = isPresent;
};



Seance.prototype.findAnnotationIndex = function(annotationId){
    var pos;
    pos = this.convocation.findAnnotationIndex(annotationId);
    if(pos > -1){
        return {
            doc: this.convocation,
            pos: pos
        }
    }
    for(var iP = 0, lP = this.getProjets().length; iP < lP; iP++){
        var projet = this.getProjets()[iP];
        pos = projet.findAnnotationIndex(annotationId);
        if(pos > -1){
            return {
                doc: projet,
                pos: pos
            }
        }
        for(var iA = 0, lA = projet.getAnnexes().length; iA < lA; iA++) {
            var annexe = projet.getAnnexes()[iA];
            pos = annexe.findAnnotationIndex(annotationId)
            if (pos > -1) {
                return {
                    doc: annexe,
                    pos: pos
                }

            }
        }

    }

    return null;
}



Seance.prototype.deleteAllAnnotations = function(){
    this.convocation.deleteAllAnnotations();
    for(iP = 0, lP = this.getProjets().length; iP < lP; iP++ ){
        var projet =  this.getProjets()[iP];
        projet.deleteAllAnnotations();
        for(iA =0, lA = projet.getAnnexes().length; iA < lA; iA++){
            projet.getAnnexes()[iA].deleteAllAnnotations();
        }
    }
}

Seance.prototype.getDate = function () {
    return this.date;
};



