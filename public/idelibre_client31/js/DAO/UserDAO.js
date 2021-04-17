/**
 * @constructor
 * @returns {UserDAO}
 */
var UserDAO = function(){};


/**
 * désérialisation d'un Json de type User
 * @param {User} user
 * @returns {User}
 */
UserDAO.prototype.unserialize = function (user) {

    user.__proto__ = User.prototype;

    return user;
};
