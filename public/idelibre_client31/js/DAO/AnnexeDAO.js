/**
 * @constructor
 * @returns {AnnexeDAO}
 */
var AnnexeDAO = function(){};

/**
 * désérialisation d'un Json de type Annexe
 * @param {Annexe} annexe
 * @returns {Annexe}
 */
AnnexeDAO.prototype.unserialize = function (annexe) {

    annexe.__proto__ = Annexe.prototype;

    return annexe;
};
