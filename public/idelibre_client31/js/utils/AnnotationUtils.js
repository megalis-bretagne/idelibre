var AnnotationUtils = function () {
    var DPI = 72;
    var annotationUtils = {}


    annotationUtils.annotationToStickyNote = function (annotation, userId) {
        var stickyNote = new SitckyNote();
        stickyNote.id = annotation.id;
        stickyNote.authorId = annotation.authorId;
        stickyNote.authorName = annotation.authorName;
        stickyNote.page = annotation.page;
        stickyNote.rect = annotationRectToStickyRect(annotation.rect);
        stickyNote.text = annotation.text;
        stickyNote.sharedUserIdList = annotation.sharedUserIdList;
        stickyNote.timestamp = annotation.date;
        if (annotation.authorId != userId) {
            stickyNote.isLocked = true;
        }
        ;
        return stickyNote;
    };
    annotationUtils.stickyNoteToAnnotation = function (stickynote, userId) {
        var annotation = new Annotation(stickynote.id, stickynote.authorId, stickynote.authorName, stickynote.text, stickyRectToAnnotationRect(stickynote.rect), stickynote.page, stickynote.sharedUserIdList, stickynote.timestamp);
        if (stickynote.authorId === userId) {
            annotation.isRead = true;
        }
        return annotation;
    };
    var annotationRectToStickyRect = function (annotationRect) {
        var x = annotationRect.left * DPI;
        var y = annotationRect.top * DPI;
        var width = (annotationRect.right - annotationRect.left) * DPI;
        var height = (annotationRect.bottom - annotationRect.top) * DPI;
        return {x: x, y: y, width: width, height: height};
    };
    var stickyRectToAnnotationRect = function (stickyRect) {

        var top = stickyRect.y / DPI;
        var left = stickyRect.x / DPI;
        var right = (stickyRect.x + stickyRect.width) / DPI;
        var bottom = (stickyRect.y + stickyRect.height) / DPI;
        return {bottom: bottom, left: left, right: right, top: top};
    };


    annotationUtils.getServerFormated = function (annotation) {
        return JSON.stringify({
            annotation_author_id: annotation.authorId,
            annotation_author_name: annotation.authorName,
            annotation_date: annotation.date,
            annotation_id: annotation.id,
            annotation_page: annotation.page - 1,
            annotation_rect: annotation.rect,
            annotation_shareduseridlist: annotation.sharedUserIdList,
            annotation_text: annotation.text,
            originType : annotation.originType,
            originId: annotation.originId
        });
    };



    return annotationUtils;
}();