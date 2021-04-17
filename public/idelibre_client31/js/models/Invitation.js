
/**
 *
 * @param {uuid} id
 * @param {boolean} isRead
 * @param {Document_text} document_text
 * @returns {Convocation}
 */
var Invitation = function (id, isRead, document_id) {
    this.id = id;
    this.isRead = isRead;
    this.document_id = document_id;
    this.isLoaded = NOTLOADED ;  //NOTLOADED PENDING LOADED
};
