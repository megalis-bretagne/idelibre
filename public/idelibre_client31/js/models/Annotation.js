/**
 * @constructor
 * @returns {Annotation}
 */
var Annotation = function (id, authorId, authorName, text, rect, page, sharedUserIdList, date) {
   this.id =id; 
   this.authorId = authorId;
   this.authorName = authorName;
   this.text = text;
   this.rect = rect;
   this.page = page;
   this.sharedUserIdList = sharedUserIdList;
   this.date = date;
   this.isRead;
   this.originType;
   this.originId;
};

Annotation.prototype.authorId ="";
Annotation.prototype.authorName ="";
Annotation.prototype.page = 0;
Annotation.prototype.rect = {};
Annotation.prototype.text ="";
Annotation.prototype.sharedUserIdList ="";
Annotation.prototype.ownerId ="";
Annotation.prototype.ownerType ="";

Annotation.prototype.setOrigin = function(doc, account){


    this.originType = doc.getType();


    if(doc.getType() == DocType.PROJET){
        this.originId = doc.id;
    }else if (doc.getType() == DocType.ANNEXE){
        this.originId = doc.annexe_id;
    }else if (doc.getType() ==  DocType.CONVOCATION){
        var seance = account.findSeanceByConvocationId(doc.id);
        if (seance){
            this.originId = seance.id;
        }

    }


/*
    if(doc.id) {
        this.originId = doc.id;
    }else{
        this.originId = doc.annexe_id;
    }*/
}
