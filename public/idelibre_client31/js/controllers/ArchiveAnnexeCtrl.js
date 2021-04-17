(function () {
    'use strict';

    angular.module('idelibreApp').controller('ArchiveAnnexeCtrl', function ($scope, $routeParams, $http, $location, accountSrv, dlOriginalSrv, usSpinnerService, $timeout, $rootScope, $log) {
      
      $rootScope.$broadcast('buttonDrawersVisibility',{visibility: false});
        
        $scope.accountId = $routeParams.accountId;
        $scope.seanceId = $routeParams.seanceId;
        $scope.annexeId = $routeParams.annexeId;
        $scope.projetId = $routeParams.projetId;
        $scope.typeDocument = "archivedAnnexe";

        var account = accountSrv.findAccountById($scope.accountId);
        var collectivite = account.name;

        $scope.goToArchiveSeance = function () {
            $location.path('/archiveSeance/' + $scope.accountId + '/' + $scope.seanceId);
        };

        $scope.dlStatus = false;
        $scope.downloadAnnexe = function () {
            if ($scope.dlStatus === false) {
                $scope.dlStatus = true;
                $scope.stateClass = "fa fa-spinner fa-spin fa-lg ";
                var url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + $scope.annexeId;
                dlOriginalSrv.dlPDF(account, url, "archivedAnnexe" + $scope.annexeId , "mime", "idelibre/" + "archived", callbackSuccess, callbackError);
            }
        };


        var callbackSuccess = function () {
            $scope.dlStatus = false;
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
        }


        var callbackError = function () {
            $scope.dlStatus = false;
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement erreur', content: 'Votre document n\'a pas été téléchargé'});
        };







    });
})();