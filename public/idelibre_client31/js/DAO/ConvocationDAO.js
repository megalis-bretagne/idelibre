/**
 * @constructor
 * @returns {ConvocationDAO}
 */
var ConvocationDAO = function () {
};



/**
 * désérialisation d'un Json de type Convocation
 * @param {Convocation} convocation
 * @returns {Convocation}
 */
ConvocationDAO.prototype.unserialize = function (convocation) {
    
    var document_textDAO = new Document_textDAO();
    
    convocation.__proto__ = Convocation.prototype;
    if(convocation.document_text){
        
        convocation.document_text = document_textDAO.unserialize(convocation.document_text);
    }
    
    return convocation;

};
