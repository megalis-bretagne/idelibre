(function () {
    //TODO non fini juste un copy paste d'un autre
    'use strict';
    angular.module('idelibreApp').directive('archiveprojetBtn', function ($location, $http, $rootScope, loginSrv, accountSrv) {
        /**
         * @namespace projetBtn
         */
        return {
            templateUrl: 'js/directives/archiveprojetBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                seanceid: '=',
                accountid: '=',
                projet: '=',
                state: '='

            },
            /**
             * 
             * @param {type} $scope
             * @function projetBtn.controller
             */
            controller: function ($scope) {
                
                var account = accountSrv.findAccountById($scope.accountid);
                
                $scope.goToProjet = function () {
                    if (account.status == ONLINE) {
                        $location.path('/archiveprojet/' + $scope.projet.projet_id + '/' + $scope.projet.projet_document_id + '/' + $scope.accountid + '/' + $scope.seanceid);
                    }
                };

            }
            
        };
    });
})();
