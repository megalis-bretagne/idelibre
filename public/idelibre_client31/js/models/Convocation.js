
/**
 * 
 * @param {uuid} id
 * @param {boolean} isRead
 * @param {Document_text} document_text
 * @returns {Convocation}
 */
var Convocation = function (id, isRead, document_text) {
    this.id = id;
    this.isRead = isRead;
    this.document_text = document_text;



};

/**
 * attribuer un document à la convocation
 * @param {Document_text} document_text
 * @returns {}
 */
Convocation.prototype.setDocument_text = function (document_text) {
    this.document_text = document_text;
};




Convocation.prototype.countAnnotations = function () {
    var sharred = 0;
    var private = 0;
    _.each(this.getAnnotations(), function (annotation) {
        if (_.isEmpty(annotation.sharedUserIdList)) {
            private++;
        } else {
            sharred++;
        }
    });

    return({private: private, sharred: sharred});
};



Convocation.prototype.addAnnotation = function (annotation) {
    if (!this.annotations)
        this.annotations = [];

    var index = _.findIndex(this.annotations, function (annots) {
        return annots.id === annotation.id;
    });
    
    if (index === -1) {
        //it's a new annotation : 
        this.annotations.push(annotation)
    }else{
        //it's an updated annotation
        this.annotations[index] = annotation;
    }

    
};

Convocation.prototype.getAnnotations = function (annotation) {
    if (!this.annotations)
        this.annotations = [];
    return this.annotations;
};



Convocation.prototype.deleteAnnotation = function (annotationId) {
    if (!this.annotations)
        this.annotations = [];

    var index = _.findIndex(this.annotations, function (annots) {
        return annots.id === annotationId;
    });
    if (index === -1) {
        return false;
    }else{
        this.annotations.splice(index , 1);
        return true;
    }
};


Convocation.prototype.getType = function(){
    return DocType.CONVOCATION;
}


Convocation.prototype.findAnnotationIndex = function (annotationId) {
    var pos = _.findIndex(this.getAnnotations(), function (annotation) {
        return annotation.id === annotationId;
    });
    return pos;
};



Convocation.prototype.deleteAllAnnotations = function(){
    this.annotations =[];
}








