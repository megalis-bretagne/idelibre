(function () {
    //TODO non fini juste un copy paste d'un autre
    'use strict';
    angular.module('idelibreApp').directive('archiveseanceBtn', function ($location, accountSrv) {

        /**
         * @namespace projetBtn
         */
        return {
            templateUrl: 'js/directives/archiveseanceBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                seance: '=',
                accountid: '=',
                state: '='

            },
            /**
             * 
             * @param {type} $scope
             * @function projetBtn.controller
             */
            controller: function ($scope) {
                $scope.date = DateUtils.formatedDateWithTime($scope.seance.seance_date);
                $scope.name = $scope.seance.seance_name;
                
                var account = accountSrv.findAccountById($scope.accountid);
                
                $scope.goToSeance = function () {
                    if (account.status == ONLINE) {
                        $location.path('/archiveSeance/' + $scope.accountid + '/' + $scope.seance.seance_id);
                    }
                };

            }

        };

    });


})();
