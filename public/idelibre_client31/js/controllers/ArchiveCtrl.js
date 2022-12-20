(function () {
    'use strict';

    angular.module('idelibreApp').controller('ArchiveCtrl', function ($scope, $log, $rootScope, $routeParams, $http, $location, accountSrv, usSpinnerService, $timeout, socketioSrv) {

$rootScope.$broadcast('buttonDrawersVisibility',{visibility: false});

        var startSpin = function () {
            usSpinnerService.spin('spinner-1');
        };
        var stopSpin = function () {
            usSpinnerService.stop('spinner-1');
        };

        $timeout(startSpin, 0);

        $scope.accountId = $routeParams.accountId;
        var account = accountSrv.findAccountById($scope.accountId);


        // on envoie le nom de la apge
        $rootScope.$broadcast('name', {
            name: 'Séances classées'
        });

        $scope.archivedSeances = null;

        //pour ne pas afficher de texte si pas de séances archivées
        $scope.isToShow = !_.isEmpty($scope.archivedSeances);

        //ask for archiveSeances list
        socketioSrv.archivedSeancesList(account);

        $scope.sortSeance = 'seance_date';
        $scope.sortReverse = 'desc';
        /**
         * fonction de tri des Séances
         * @param {type} arg
         * @returns {undefined}
         */
        $scope.sortBy = function (arg) {
            if ($scope.sortSeance == arg) {
                $scope.sortSeance = '-' + $scope.sortSeance;
            } else {
                $scope.sortSeance = arg;
            }
        };

        $scope.goToSeances = function () {
            $location.path('/seance/' + $scope.accountId);
        };

        $scope.$on('archivedSeancesList', function (event, data) {
            $scope.archivedSeances = data.archivedSeances;
            stopSpin();
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        });

    });

})();
