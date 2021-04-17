/**
 * @constructor
 * @returns {AccountDAO}
 */
var AccountDAO = function () {
};


/**
 * désérialisation d'un Json de type Account
 * @param {Account} account
 * @returns {Account}
 */
AccountDAO.prototype.unserialize = function (account) {

    account.__proto__ = Account.prototype;

    var seanceDAO = new SeanceDAO();

    // deserialisation des projets

    if (account.seances) {
        for (var i = 0; i < account.seances.length; i++) {

            account.seances[i] = seanceDAO.unserialize(account.seances[i]);
        }
        
    }

    return account;
};




