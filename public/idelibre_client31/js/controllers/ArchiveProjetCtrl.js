(function () {
    'use strict';

    angular.module('idelibreApp').controller('ArchiveProjetCtrl', function ($rootScope, $scope, $routeParams, $location, $log, accountSrv, dlOriginalSrv, usSpinnerService, $timeout) {
      
        $rootScope.$broadcast('buttonDrawersVisibility',{visibility: false});
        
        $scope.accountId = $routeParams.accountId;
        $scope.seanceId = $routeParams.seanceId;
        $scope.documentId = $routeParams.documentId;
        $scope.projetId = $routeParams.projetId;

        var account = accountSrv.findAccountById($scope.accountId);
        var collectivite = account.name;
        
        $scope.typeDocument = "archivedProjet"

        //spinner
        var startSpin = function () {
            usSpinnerService.spin('spinner-1');
        };
        var stopSpin = function () {
            usSpinnerService.stop('spinner-1');
        };

        //démarage du spinner
        //$timeout(startSpin, 0);

        $scope.goToArchiveSeance = function () {
            $location.path('/archiveSeance/' + $scope.accountId + '/' + $scope.seanceId);
        };


//////////////////////////////////////////////////////


        var callbackSuccess = function () {
            $scope.dlStatus = false;
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
        }

        var callbackError = function () {
            $scope.dlStatus = false;
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement erreur', content: 'Votre document n\'a pas été téléchargé'});
        };

        $scope.dlStatus = false;
        //Téléchargement du document
        $scope.downloadDocument = function () {

            if ($scope.dlStatus === false) {
                $scope.dlStatus = true;

                dlOriginalSrv.dlPDF(loginSrv.full_url + '/idelibre21/nodejs/getDocument/' + $scope.documentId + '/' + collectivite, 'archiveProjet' + $scope.documentId + '.pdf', 'application/pdf', "idelibre/archive", callbackSuccess, callbackError);

            }
        };
        
        
          $scope.downloadDocument = function () {

            if ($scope.dlStatus === false) {
                $scope.dlStatus = true;
                var url = account.url + '/nodejs/' + config.API_LEVEL + '/projets/dlPdf/' + $scope.documentId;
                var filename = $scope.projetId + '.pdf';
                dlOriginalSrv.dlPDF(account, url, filename, 'application/pdf', "idelibre/" +"archived", callbackSuccess, callbackError);
            }
        };
        

        $scope.goo = function () {
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
        };


    });
})();
