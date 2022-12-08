/**
 * @constructor
 * @returns {OtherdocDAO}
 */
var OtherdocDAO = function () {
};



/**
 * désérialisation d'un Json de type Otherdoc
 * @param {Otherdoc} otherdoc
 * @returns {Otherdoc}
 */
OtherdocDAO.prototype.unserialize = function (otherdoc) {

    var document_textDAO = new Document_textDAO();

    otherdoc.__proto__ = Otherdoc.prototype;

    //restoration des documents
    if (otherdoc.document_text) {
        otherdoc.document_text = document_textDAO.unserialize(otherdoc.document_text);
    }

    return otherdoc;
};









