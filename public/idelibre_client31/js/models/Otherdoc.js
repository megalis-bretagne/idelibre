/**
 *
 * @param {string} id
 * @param {string} name
 * @param {Document_text} document_text
 * @param {int} rank
 * @returns {Otherdoc}
 */
var Otherdoc = function (id, name, rank, document_text ) {
    this.id = id;
    this.name = name;
    this.rank = rank;
    this.document_text = document_text;
};

/**
 * attribuer un document
 * @param {Document_text} document_text
 * @returns {}
 */
Otherdoc.prototype.setDocument_text = function (document_text) {
    this.document_text = document_text;
};

/**
 * renvoie true si le projet est deja accessible en local
 * @returns {Boolean}
 */
Otherdoc.prototype.isLoaded = function () {
    if (this.document_text) {
        return this.document_text.isLoaded;
    }
    //on retourne true si pas de doc associé au projet
    else {
        return true;
    }

};




/**
 * nombre de partie de pdf de projet chargée
 * @returns {undefined}
 */
Otherdoc.prototype.countOtherdocPdfdatasLoaded = function () {
    var num = 0;
    _.each(this.document_text.pdfdatas, function (pdf) {
        if (pdf.isLoaded) {
            num++;
        }
    });
    return num;
};

Otherdoc.prototype.getType = function(){
    return DocType.OTHERDOC;
}

