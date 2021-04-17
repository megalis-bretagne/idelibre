(function () {


    angular.module('idelibreApp').factory('sslCheckerSrv', function () {


        var sslChecker = {};

        /**
         * verifie que le certificat résenté par le servuer soit le bon
         * @param {type} serveurUrl
         * @returns {undefined}
         */
        sslChecker.check = function (serveurUrl, successCallback, errorCallback ) {
            var sslChecker = window.plugins.sslCertificateChecker;
            sslChecker.check(successCallback, errorCallback, serveurUrl);
        };
        
        
        return sslChecker;
    });


})();


