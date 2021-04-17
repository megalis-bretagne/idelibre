/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var PdfdataDAO = function(){};

/**
 * désérialisation d'un Json de type Annexe
 * @param {Pdfdata} pdfdata;
 * @returns {Pdfdata}
 */
PdfdataDAO.prototype.unserialize = function (pdfdata) {

    pdfdata.__proto__ = Pdfdata.prototype;
    
    pdfdata.isLoaded = false;

    return pdfdata;
};
