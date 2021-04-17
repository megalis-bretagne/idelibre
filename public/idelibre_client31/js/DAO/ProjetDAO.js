/**
 * @constructor
 * @returns {ProjetDAO}
 */
var ProjetDAO = function () {
};



/**
 * désérialisation d'un Json de type Projet
 * @param {Projet} projet
 * @returns {Projet}
 */
ProjetDAO.prototype.unserialize = function (projet) {

    var annexeDAO = new AnnexeDAO();
    var document_textDAO = new Document_textDAO();

    projet.__proto__ = Projet.prototype;

    //restoration des annexes
    if (projet.annexes) {
        for (var i = 0; i < projet.annexes.length; i++) {
            projet.annexes[i] = annexeDAO.unserialize(projet.annexes[i]);
        }
    }

    //restoration des documents
    if (projet.document_text) {
        projet.document_text = document_textDAO.unserialize(projet.document_text);
    }

    return projet;
};









