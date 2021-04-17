/**
 * @constructor
 * @returns {InvitationDAO}
 */
var InvitationDAO = function () {
};



/**
 * désérialisation d'un Json de type Convocation
 * @param {Invitation} invitation
 * @returns {Invitation}
 */
InvitationDAO.prototype.unserialize = function (invitation) {

    invitation.__proto__ = Invitation.prototype;
    var document_textDAO = new Document_textDAO();

    if(invitation.document_text){
        invitation.document_text = document_textDAO.unserialize(invitation.document_text);
    }


    return invitation;

};
