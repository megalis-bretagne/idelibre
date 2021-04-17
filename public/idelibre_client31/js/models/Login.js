
/**
 * 
 * @param {uuid} id
 * @param {string} username
 * @param {string} password
 * @returns {Login}
 */
var Login = function (id, username, password, suffix) {
    this.id = id;
    this.username = username;
    this.password = password;
    this.suffix = suffix;
    this.accounts = [];

    /**
     * @property {String} adresse du server pour l'application Cordova
     */
    this.server_url = null;


    /**
     * @property {Number} numero de la revision de l'account (pour verifier si des comptes ont été ajoutés / supprimés
     */
    this.rev = null;



};

/**
 * Supprime tous les comptes
 * @returns {undefined}
 */
Login.prototype.removeAllAccounts = function () {
    this.accounts = [];
};


/**
 * renvoie la listes des accounts
 * @returns {Array of Accounts}
 */
Login.prototype.getAccounts = function () {
    this.accounts = this.accounts || [];
    return this.accounts;
};


/**
 * ajoute un account à un objet login
 * @param {Account} account
 * @returns {}
 */
Login.prototype.addAccount = function (account) {
    var accountsList = this.accounts;
    var alreadyIn = _.find(accountsList, function (el) {
        return el.id === account.Id;
    });
    if (alreadyIn){
        return;
    }
    this.accounts.push(account);
};

/**
 * 
 * @param {String} username
 * @param {String} password
 * @param {String} server_url
 * @returns {}
 */
Login.prototype.setLogin = function (username, password, server_url) {
    //TODO : verification de la coherence username / mdp !
    this.username = username;
    this.password = password;
    this.server_url = server_url || null;

};



/**
 * rempli le login avec les donnée recu du serveur (nom, prenom, revision, 
 * @param {LoginJson} data
 * @returns {}
 */
Login.prototype.setDetail = function (data) {
    // si le numero de revison est différent

    // si rev ou id différent
    if (this.rev !== data.rev || this.id !== data.id) {


        this.id = data.id;
        this.firstname = data.firstname;
        this.lastname = data.lastname;

        this.setAccounts(data);
    }

};


/**
 * retourne l'Account correspondant à l'id
 * @param {String} accountId
 * @returns {Account}
 */
Login.prototype.findAccount = function (accountId) {
    //on récupere l'account correspondant à ces seances TODO : en faire une methode de login
    var account = _.find(this.getAccounts(), function (account) {
        return account.id === accountId;
    });
    return account;
};


/**
 * accroche les accounts correspondant au login
 * @param {LoginJson} data
 * @returns {}
 */
Login.prototype.setAccounts = function (data) {


    this.rev = data.rev;
    // on supprime tous les accounts
    this.accounts = [];


    var accountDAO = new AccountDAO();

    // on lui ajoute tous les accounts presents
    if (data.accounts) {
        for (var i = 0, ln = data.accounts.length; i < ln; i++) {
            var accountFor = data.accounts[i];
            // désérialisation de l'account
            var accountJson = {
                firstname: accountFor.firstname,
                lastname: accountFor.lastname,
                id: accountFor.id,
                username: accountFor.login,
                password: accountFor.password,
                name: accountFor.login_suffix
            };

            var account = accountDAO.unserialize(accountJson);
            //var account = accountDAO.unserialize(data.accounts[i]);
            this.addAccount(account);
        }
    }
};



/**
 * reche un projet en fonction de son id
 * @param {type} projetId
 * @returns {Projet}
 */
Login.prototype.findProjet = function (projetId) {

    for (var i = 0, l = this.getAccounts().length; i < l; i++) {

        var res = this.getAccounts()[i].findProjet(projetId);
        if (res) {
            return res;
        }
    }

};

