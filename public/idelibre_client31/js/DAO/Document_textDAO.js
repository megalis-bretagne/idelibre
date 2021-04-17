/**
 * @constructor
 * @returns {Document_textDAO}
 */
var Document_textDAO = function () {
};


/**
 * désérialisation d'un Json de type Document_text
 * @param {Document_text} document_text
 * @returns {Document_text}
 */
Document_textDAO.prototype.unserialize = function (document_text) {
    var annotationDAO = new AnnotationDAO();
    var pdfdataDAO = new PdfdataDAO();

    document_text.__proto__ = Document_text.prototype;

    //on passe le document en not loaded
    document_text.isLoaded = NOTLOADED;

    //cast des annotation
    /* if (document_text.annotations) {
     for (var i = 0; i < document_text.annotations.length; i++) {
     document_text.annotations[i].__proto__ = annotationDAO.unserialize(document_text.annotations[i]);
     }
     }else {*/

    if (_.size(document_text.annotations) > 0) {
        _.each(document_text.annotations, function (doc) {
            document_text.annotations[doc.id] = doc;
        });

    } else {
        document_text.annotations = {};
    }

    //restoration des pdfdatas
    if (document_text.pdfdatas) {
        
        for (var i = 0; i < document_text.pdfdatas.length; i++) {
            //console.log('projet');
           document_text.pdfdatas[i] = pdfdataDAO.unserialize(document_text.pdfdatas[i]);
        }
    }


    /*}*/

    //cast des pdfdatas
    /*  for (var i = 0; i < document_text.getPdfdatas().length; i++) {
     document_text.pdfdatas[i].__proto__ = pdfdataDAO.unserialize(document_text.pdfdatas[i]);
     */  // }


    return document_text;
};


