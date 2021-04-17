
(function () {
    'use strict';

    angular.module('idelibreApp').controller('ArchiveSeanceCtrl', function ($routeParams, $scope, $location, accountSrv, socketioSrv, usSpinnerService, $timeout, $log, $rootScope) {


$rootScope.$broadcast('buttonDrawersVisibility',{visibility: false});

        $scope.accountId = $routeParams.accountId;
        $scope.seanceId = $routeParams.seanceId;

        var account = accountSrv.findAccountById($scope.accountId);

        var startSpin = function () {
            usSpinnerService.spin('spinner-1');
        };
        var stopSpin = function () {
            usSpinnerService.stop('spinner-1');
        };


        $timeout(startSpin, 0);


        //renvoie la liste des projets pour la seance donnée
        socketioSrv.archivedProjets(account, $scope.seanceId);

        var currentOrder = 'rank';
        /**
         * 
         * @param {string} param propriété sur laquelle on trie
         * @returns {array}
         */
        $scope.order = function (param) {
            if (param === currentOrder) {
                $scope.archivedProjets = $scope.archivedProjets.reverse();
            } else {
                $scope.archivedProjets = _.sortBy($scope.archivedProjets, function (projet) {
                    return projet[0][param];
                });
                currentOrder = param;
            }
        };


        $scope.goToArchive = function () {
            $location.path('/archive/' + $scope.accountId);
        };


        $scope.$on('archivedProjetsList', function (event, data) {
            $scope.archivedProjets = data.archivedProjets;
            stopSpin();
            if (!$rootScope.$$phase) {
                $scope.$apply();
            }
        });


    });
})();
