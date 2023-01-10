/**
 * @constructor
 * @param {uuid} id
 * @param {string} name
 * @param {int} rank
 * @returns {Annexe}
 */
var Annexe = function(annexe_id, annexe_name, annexe_rank){
  this.annexe_id = annexe_id;
  this.name = annexe_name;
  this.rank = annexe_rank;
};



Annexe.prototype.getType = function(){
    return DocType.ANNEXE;
}


// Annexe.prototype.countAnnotations = function () {
//     var sharred = 0;
//     var private = 0;
//     _.each(this.getAnnotations(), function (annotation) {
//         if (_.isEmpty(annotation.sharedUserIdList)) {
//             private++;
//         } else {
//             sharred++;
//         }
//     });
//
//     return({private: private, sharred: sharred});
// };


Annexe.prototype.countAnnotations = function () {
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




Annexe.prototype.addAnnotation = function (annotation) {
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



Annexe.prototype.getAnnotations = function (annotation) {
    if (!this.annotations)
        this.annotations = [];
    return this.annotations;
};



Annexe.prototype.deleteAnnotation = function (annotationId) {
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



Annexe.prototype.findAnnotationIndex = function (annotationId) {
    var pos = _.findIndex(this.getAnnotations(), function (annotation) {
        return annotation.id === annotationId;
    });
    return pos;
};



Annexe.prototype.deleteAllAnnotations = function(){
    this.annotations =[];
}