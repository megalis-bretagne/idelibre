/**
 * @constructor
 * @param {uuid} id
 * @param {string} firstname
 * @param {string} lastname
 * @param {string} username
 * @returns {User}
 */
var User = function(id, firstname, lastname, username){
    this.id = id;
    this.firstname = firstname;
    this.lastname= lastname;
    this.username = username;
};

