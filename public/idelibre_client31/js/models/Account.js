/**
 * @constructor
 * @param {uuid} id
 * @param {string} suffixe nom du compte (mairie, departement, ...)
 * @returns {Account}
 */
var Account = function () {
    this.id;
    this.name;
    this.url = "";
    this.type = "";

    /**
     * @property {array of Seance} array de seances
     */
    this.seances = [];


    /**
     * @property {String} login de connection à la collectivité (login@collectivite)
     */
    this.username = null;

    /**
     * @property {String} hash du password de connection à la collectivité
     */
    this.password = null;


    /**
     * @property {String} base de donnée à laquelle on se connecte pour cette collectivité
     */
    this.suffix = null;

    /**
     * @property {String} état de la connection (connecté, déconnecté, connecting ...)
     */
    this.status = null; //pas besoin


    /**
     * @property {Number} nombre de projets chargés (utiliser getNumberLoadedProjet pour le lire)
     */
    this.numberLoadedProjets = 0;


    /**
     * @property {Number} nombre de convocations chargées
     */
    this.numberLoadedConvocations = 0;


    /**
     * @property {Number} nombre d'autres documents chargés (utiliser getNumberLoadedOtherdocs pour le lire)
     */
    this.numberLoadedOtherdocs = 0;
};


/**
 * retourne le tableau des seances si il existe un tableau vide si non
 * @returns {array of Seance}
 */
Account.prototype.getSeances = function () {
    this.seances = this.seances || [];
    return this.seances;
};


/**
 * retourne le nombre de projets chargés
 * @returns {Number}
 */
Account.prototype.getNumberLoadedProjets = function () {
    this.numberLoadedProjets = this.numberLoadedProjets || 0;
    return this.numberLoadedProjets;
};


/**
 * retourne le nombre d'autres documents chargés
 * @returns {Number}
 */
Account.prototype.getNumberLoadedOtherdocs = function () {
    this.numberLoadedOtherdocs = this.numberLoadedOtherdocs || 0;
    return this.numberLoadedOtherdocs;
};


/**
 * retourne le nombre de convocations deja chargées
 * @returns {Number}
 */
Account.prototype.getNumberLoadedConvocations = function () {
    this.numberLoadedConvocations = this.numberLoadedConvocations || 0;
    return this.numberLoadedConvocations;
};


/**
 * nombre de seances total
 * @returns {Number} nombre de seances
 */
Account.prototype.countSeances = function () {
    var seances = this.getSeances();
    return seances.length;
};


/**
 * Nombre de projet par account
 * @returns {Number}
 */
Account.prototype.countProjets = function () {
    var nbProjet = 0;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        nbProjet += this.seances[i].countProjets();
    }
    return nbProjet;

};


/**
 * Nombre d'uatres documents par account
 * @returns {Number}
 */
Account.prototype.countOtherdocs = function () {
    var nbOtherdoc = 0;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        nbOtherdoc += this.seances[i].countOtherdocs();
    }
    return nbOtherdoc;

};

/**
 *
 * TODO à supprimer normalement inutiele maintenant
 * compte le nombre de projets chargés
 * @returns {Number}
 */
Account.prototype.countLoadedProjets = function () {
    var nbProjet = 0;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        nbProjet += this.seances[i].countLoadedProjets();
    }
    return nbProjet;

};


/**
 * a faire aussi coté serveur comme le formatDataSeance
 * @param {Json} data
 * @returns {ProjetJson}
 */
Account.prototype.formatDataProjet = function (data) {

    var projet = {
        id: data.projet_id,
        name: data.projet_name,
 //       theme: data.ptheme.ptheme_name,
        rank: data.projet_rank,
        annexes: data.annexes,
        //vote: data.vote,
        document_text: {
            id: data.projet_document_id,
            name: data.projet_name,
            isLoaded: NOTLOADED,
        }
    };

    if (data.ptheme && data.ptheme.ptheme_name) {
        projet.theme = data.ptheme.ptheme_name
    }else {
        projet.theme = 'Sans thème'
    }

    if (data.user && data.user.projet_user_firstname && data.user.projet_user_lastname) {
        projet.rapporteur = data.user.projet_user_firstname + " " + data.user.projet_user_lastname
    }


    if (data.user) {
        projet.rapporteurId = data.projet_user_id
    }


    //on met une valeur non téléchargé aux annexes //FIXME annexe could b e loaded
    _.each(projet.annexes, function (annexe) {
        annexe.loaded = NOTLOADED;
    });

    return projet;

};


/**
 * a faire aussi coté serveur comme le formatDataSeance
 * @param {Json} data
 * @returns {OtherdocJson}
 */
Account.prototype.formatDataOtherdoc = function (data) {

    var otherdoc = {
        id: data.otherdoc_id,
        name: data.otherdoc_name,
        rank: data.otherdoc_rank,
        document_text: {
            id: data.otherdoc_documentId,
            name: data.otherdoc_name,
            isLoaded: NOTLOADED,
        }
    };

    return otherdoc;

};


/**
 * trouve la convocation correspondante à l'userchr
 * @param {Convocation} convocations
 * @returns {undefined}
 */
Account.prototype.findConvocation = function (convocations) {
    var id = this.id;
    var convocation = _.find(convocations, function (convocation) {
        return (convocation.user_id === id);
    });
    return convocation;
};


/**
 * Formates les annotations provenant du serveur
 * @param {type} annotations
 * @returns {undefined}
 */
Account.prototype.formatAnnotations = function (annotations) {
    var that = this;
    // debugger;
    _.each(annotations, function (annot) {
        //resuperation du projet correspondant
        var res = that.findProjet(annot.projet_id);
        res.document_text.annotations[annot.annot_id] = JSON.parse(annot.json);
    });

};


/**
 * format les donnée de la seance recu du webservice de maniere correcte peut etre judicieux de le faire coté serveur ?
 * @param {Json} data
 * @returns {SeanceJson}
 */
Account.prototype.formatDataSeance = function (data) {


// recherche de la convocation correspondant à cet utilisateur
//    var convocation = this.findConvocation(data.Convocation);

    var seance = {
        id: data.seance_id,
        rev: data.seance_rev,
        name: data.seance_name,
        date: data.seance_date,
        presentStatus: data.presentStatus,
        isRemoteStatus: data.isRemoteStatus,
        // convocation: {
        //     id: data.convocation.document_id,
        //     isRead: data.convocation.isRead,
        //     document_text: {
        //         id: data.seance_document_id,
        //         name: "Convocation",
        //         isLoaded: NOTLOADED,
        //         //pdfdatas: data.Document.Pdfdata
        //         /* pdfdatas: _.each(data.Document.Pdfdata, function (pdf) {
        //          pdf.isLoaded = false;
        //          })*/
        //
        //         // pas d'annotation car convocation
        //     }
        // },
        //  users: data.Users,
        projets: _.map(data.projets, this.formatDataProjet),
        otherdocs: _.map(data.otherdocs, this.formatDataOtherdoc)

    };


    if (data.convocation) {
        seance.convocation = {
            id: data.convocation.document_id,
            isRead: data.convocation.convocation_read,
            document_text: {
                id: data.seance_document_id,
                name: "Convocation",
                isLoaded: NOTLOADED
            }

        }
    }


    if (data.invitation) {
        seance.invitation = {
            id: data.invitation.invitation_id,
            isRead: data.invitation.invitation_read,
            document_text: {
                id: data.invitation.invitation_document_id,
                name: "Invitation",
                isLoaded: NOTLOADED
            }

        }
    }

    return seance;


};


/**
 * ajouter une seance à l'Account
 * @param {array of Seance} seances
 * @returns {}
 */
Account.prototype.addSeances = function (seances) {

    //on déserialize la séance
    var seanceDao = new SeanceDAO();
    if (seances) {
        for (iS = 0, lnS = seances.length; iS < lnS; iS++) {

            var seance = seances[iS];

            ////////////////////////////
            seance = this.formatDataSeance(seance);
            seance = seanceDao.unserialize(seance);

            //on l'ajoute
            this.getSeances().push(seance);
        }
    }
};


/**
 *
 * supression de seances d'un account
 * @param {string} seancesId
 * @returns {}
 */
Account.prototype.removeSeances = function (seancesToRemove) {
    for (var iS = 0, lnS = seancesToRemove.length; iS < lnS; iS++) {
        this.removeSeance(seancesToRemove[iS].seanceId);
    }

};

/**
 * supression d'une seance
 * @param {String} seanceId
 * @returns {undefined}
 */
Account.prototype.removeSeance = function (seanceId) {
    for (var iS = this.getSeances().length - 1; iS >= 0; iS--) {
        if (this.seances[iS].id === seanceId) {
            this.seances.splice(iS, 1);

        }
    }

};


/**
 *  nombre de convocations non lues
 * @returns {Number}
 */
Account.prototype.countUnreadConvocation = function () {
    var nbUnread = 0;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        if (!this.seances[i].isUnreadConvocation()) {
            nbUnread++;
        }
    }
    return nbUnread;
};


/**
 * nombre de seances modifiée
 * @returns {Number}
 */
Account.prototype.countModifiedSeances = function () {
    var nbModified = 0;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        if (this.seances[i].isModified) {
            nbModified++;
        }
    }
    return nbModified;
};


/**
 * nombre de convocation déja chargées
 * @returns {Number}
 */
Account.prototype.countLoadedConvocationDocument = function () {
    var nbLoaded = 0;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        if (this.seances[i].isLoadedConvocationDocument()) {
            nbLoaded++;
        }
    }
    return nbLoaded;


};


/**
 * cherche la seance correspondante à l'id et la renvoie
 * @param {String} seanceId
 * @returns {Seance}
 */
Account.prototype.findSeance = function (seanceId) {
    var seance = _.find(this.getSeances(), function (seance) {
        return seance.id === seanceId;
    });
    return seance;
};


/**
 * cherche la convocation correspondante à l'id et la renvoie
 * @param {String} seanceId
 * @returns {Seance}
 */
Account.prototype.findSeanceByConvocationId = function (convocationId) {
    var seance = _.find(this.getSeances(), function (seance) {
        return seance.convocation.id === convocationId;
    });
    return seance;
};


Account.prototype.findAnnotationIndex = function (annotationId) {
    var res;
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        res = this.getSeances()[i].findAnnotationIndex(annotationId);
        if (res) {
            return res;
        }
    }
    return null;
}


/**
 * prends une liste did et retourne la liste des senaces NON UTILISE
 * @param {array of string} listeSeancesId
 * @returns {array of seances}
 */
Account.prototype.findListSeances = function (listeSeancesId) {
    var listeSeances = [];
    _.each(listeSeancesId, function (seanceId) {
        listeSeances.push(this.findSeance(seanceId));
    });
    return listeSeances;
};


/**
 * remplace une Séance d'un account par une autre
 * @param {SeanceJson} seanceServer
 * @returns {undefined}
 */
Account.prototype.replaceSeance = function (seanceServer) {
    //on format correctement la seance venant du serveur
    seanceServer = this.formatDataSeance(seanceServer);

    // on deserialize la seance venant du serveurdirectives

    var seanceDAO = new SeanceDAO();
    seanceServer = seanceDAO.unserialize(seanceServer);


    //on cherche la position de cette seance dans l'account.
    var pos;
    for (var iS = 0, lnS = this.getSeances().length; iS < lnS; iS++) {
        if (this.seances[iS].id === seanceServer.id) {
            pos = iS;
        }
    }


    //si la seance existe deja et si le numéro de la revision est différent
    if (typeof pos != "undefined" && this.seances[pos].rev != seanceServer.rev) {
        // on note la seance comme modifiée
        seanceServer.isModified = true;

        //on remplace la seance par la nouvelle
        this.seances[pos] = seanceServer;
    }
};


/**
 * Retourne un tableau de string des ids des projets
 * @returns {array of string}
 */
Account.prototype.listProjetId = function () {
    var projetList = []
    _.each(this.getSeances(), function (seance) {
        projetList.push(seance.stringProjetIds());
    });

    return _.flatten(projetList);

};

/**
 * Retourne un tableau de string des ids des autres documents
 * @returns {array of string}
 */
Account.prototype.listOtherdocId = function () {
    var otherdocList = []
    _.each(this.getSeances(), function (seance) {
        otherdocList.push(seance.stringOtherdocIds());
    });

    return _.flatten(otherdocList);

};


/**
 * retrouve un objet projet en fonction de son id
 * @param {type} projetId
 * @returns {Projet}
 */
Account.prototype.findProjet = function (projetId) {

    for (var i = 0, l = this.getSeances().length; i < l; i++) {

        var res = this.getSeances()[i].findProjet(projetId);
        if (res) {
            return res;
        }
    }

};


/**
 * retrouve un objet autre document en fonction de son id
 * @param {type} otherdocId
 * @returns {Otherdoc}
 */
Account.prototype.findOtherdoc = function (otherdocId) {

    for (var i = 0, l = this.getSeances().length; i < l; i++) {

        var res = this.getSeances()[i].findOtherdoc(otherdocId);
        if (res) {
            return res;
        }
    }

};

Account.prototype.deleteAllAnnotations = function () {
    for (var i = 0, ln = this.getSeances().length; i < ln; i++) {
        this.getSeances()[i].deleteAllAnnotations();
    }
}












