/**
 * @constructor
 * @returns {SeanceDAO}
 */
var SeanceDAO = function () {
};



/**
 * désérialisation d'un Json de type Seance
 * @param {Seance} seance
 * @returns {Seance}
 */
SeanceDAO.prototype.unserialize = function (seance) {

    var projetDAO = new ProjetDAO();
    var userDAO = new UserDAO();
    var convocationDAO = new ConvocationDAO();
    var invitationDAO = new InvitationDAO();

    seance.__proto__ = Seance.prototype;



    // deserialisation des projets
    if (seance.projets) {

        for (var i = 0; i < seance.projets.length; i++) {
            //console.log('projet');
            seance.projets[i] = projetDAO.unserialize(seance.projets[i]);
        }
    }

    // deserialisation des users
    if (seance.users) {
        for (var i = 0; i < seance.users.length; i++) {
            console.log('users');
            seance.users[i] = userDAO.unserialize(seance.users[i]);
        }
    }

    // deserialisation de la convocation
    if (seance.convocation) {
        seance.convocation = convocationDAO.unserialize(seance.convocation);
    }

    //deserialisation de l'invitation
    if (seance.invitation) {
        console.log("INTO THE IF INVITATION");
        console.log(seance.invitation);
        seance.invitation = invitationDAO.unserialize(seance.invitation);
        console.log(seance.invitation);
    }


    return seance;
};




