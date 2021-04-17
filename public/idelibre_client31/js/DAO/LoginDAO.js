/**
 * 
 * @constructor 
 * @returns {LoginDAO}
 */
var LoginDAO = function () {
};


/**
 * recherche un json de type Login depuis le localstorage
 * 
 * @returns {Projet}
 */
LoginDAO.prototype.find = function () {

    // recuperation du JSON dans le local storage
    var loginJson = JSON.parse(localStorage.getItem('loginStorage'));

    // Si pas de login enregistré on renvoie null
    if (!loginJson) {
        return null;
        // Sinon on renvoie l'objet login
    } else {
        var login = this.unserialize(loginJson);
        return login;
    }
};


/**
 * désérialisation d'un Json de type Login
 * @param {Login} login
 * @returns {Login}
 */
LoginDAO.prototype.unserialize = function (login) {

    login.__proto__ = Login.prototype;

    var accountDAO = new AccountDAO();


    // deserialisation des accounts
    if (login.accounts) {
        for (var i = 0; i < login.accounts.length; i++) {


            login.accounts[i] = accountDAO.unserialize(login.accounts[i]);
        }
    }

    return login;
};




/**
 * sérialisation d'un objet Login
 * @param {Login} login
 * @returns {String}
 */
LoginDAO.prototype.serialize = function (login) {
    return(JSON.stringify(login));
};



/**
 * sauvegarde d'un objet Login dans le localstorage
 * @param {Login} login
 * @returns {}
 */
LoginDAO.prototype.save = function (login) {
    console.log(login);
    localStorage.setItem('loginStorage', this.serialize(login));
};




