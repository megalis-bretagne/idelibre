(function () {
    'use strict';
    angular.module('idelibreApp').controller('AccueilCtrl', function ($scope, $rootScope, accountSrv, localDbSrv, socketioSrv) {

    $rootScope.$broadcast('buttonDrawersVisibility',{visibility: false});
// on envoie le nom de la page pour affichage dans la navbar
        $rootScope.$broadcast('name', {
            name: 'accueil'
        });

        var emptyAccounts = function () {
            if (_.isEmpty(accountSrv.getList())) {
                return true;
            }
            return false;
        };
        $scope.isEmptyAccounts = emptyAccounts();
        
    });

})();