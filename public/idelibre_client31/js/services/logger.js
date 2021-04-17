(function () {
    'use strict';
    angular.module('idelibreApp').factory('logger', function () {
        var log = {};
        log.debug = function (txt) {
            
            if (config.logLevel >= DEBUG) {
                console.log("DEBUG : " + txt);
            }
        };

        return(log);



    });
})();
