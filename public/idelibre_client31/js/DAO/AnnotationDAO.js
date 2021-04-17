/**
 * @constructor
 * @returns {AnnotationDAO}
 */
var AnnotationDAO = function(){};

/**
 * désérialisation d'un Json de type Annotation
 * @param {Annotation} annotation
 * @returns {Annotation}
 */
AnnotationDAO.prototype.unserialize = function(annotation){
    annotation.__proto__ = Annotation.prototype;
    
};

