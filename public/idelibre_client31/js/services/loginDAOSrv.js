(function () {
    'use strict';
    /**
     * singleton de LoginDAO
     */
    angular.module('idelibreApp').factory('loginDAOSrv', function () {
        return new LoginDAO;
    });
    
   

})();


