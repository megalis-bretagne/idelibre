(function () {
    'use strict';
    angular.module('idelibreApp').controller('ConvocationCtrl', function ($scope, $log, $rootScope, dlOriginalSrv, $routeParams, $location, $timeout, usSpinnerService, fakeUrlSrv, accountSrv, $window) {


        $rootScope.$broadcast('buttonDrawersVisibility', {visibility: true});

        $scope.documentId = $routeParams.convocationDocumentId;
        var seanceId = $routeParams.seanceId;
        var accountId = $routeParams.accountId;
        var account = accountSrv.findAccountById(accountId);
        var seance = account.findSeance(seanceId);
        $scope.seance = seance;
        $scope.account = account;
        $scope.document = seance.convocation;
        // on envoie le nom du document
        $rootScope.$broadcast('name', {
            name: seance.name
        });
        $scope.convocation = "convocation";


        if(account.type == ACTEURS) {
            $scope.users = seance.getSharedUsers(account.userId);
        }



        /**
         * renvoie à l'ordre du jour de la seance
         * @returns {undefined}
         */
        $scope.goToOdj = function () {
            $location.path('/odj/' + seanceId + '/' + accountId);
        };
        $scope.goToAccount = function () {
            $location.path('/seance/' + accountId);
        };


        //Téléchargement du pdf de la convocation
        var callbackSuccess = function () {
            $scope.dlStatus = false;
            if (config.cordova) {
                var date = seance.date.split(' ')[0];
                $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé dans le repertoire idelibre/' + date});
            } else {
                $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
            }
        };
        var callbackError = function () {
            $scope.dlStatus = false;
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement erreur', content: 'Votre document n\'a pas été téléchargé'});
        };
        $scope.dlStatus = false;
        $scope.downloadDocument = function () {
            if ($scope.dlStatus === false) {
                $scope.dlStatus = true;
                var date = formatedDate(new Date(parseInt(seance.date)));
                var url = account.url + '/nodejs/' + config.API_LEVEL + '/projets/dlPdf/' + $scope.documentId;
                var filename = 'convocation_' + seance.name + '.pdf';
                dlOriginalSrv.dlPDF(account, url, filename, 'application/pdf', "idelibre/" + date, callbackSuccess, callbackError);
            }
        };
        function formatedDate(date) {
            var month = parseInt(date.getMonth()) + 1;
            if (month < 10) {
                month = "0" + month;
            }
            var dayDate = date.getDate();
            if (dayDate < 10) {
                dayDate = "0" + dayDate;
            }
            var fdate = "" + dayDate + "/" + month + "/" + date.getFullYear();
            return fdate;
        }


    });
})();
