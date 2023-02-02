(function () {
    'use strict';

    angular.module('idelibreApp').controller('OtherdocCtrl', function ($rootScope, $scope, $routeParams, $location, dlOriginalSrv, usSpinnerService, $timeout, fakeUrlSrv, workerSrv, accountSrv) {


        workerSrv.clearDocument();

        fakeUrlSrv.removeUrls();

        $scope.accountId = $routeParams.accountId;
        $scope.seanceId = $routeParams.seanceId;
        $scope.documentId = $routeParams.documentId;
        $scope.otherdocId = $routeParams.otherdocId;

        var account = accountSrv.findAccountById($scope.accountId);
        var seance = account.findSeance($scope.seanceId);
        var otherdoc = seance.findOtherdoc($scope.otherdocId);



        $scope.seance = seance;
        $scope.account = account;
        $scope.document = otherdoc;

        // on envoie le nom du document
        $rootScope.$broadcast('name', {
            name: otherdoc.rank + 1 + "- " + otherdoc.name
        });

        $scope.toggleSearchbar = function () {
            $rootScope.$broadcast('toggle searchbar');
        };


        $scope.goToOdj = function () {
            $rootScope.$broadcast('close searchbar');
            $location.path('/odj/' + $scope.seanceId + '/' + $scope.accountId);
        };




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
                var date = formatedDate(new Date(parseInt(seance.date)));
                var url = account.url + '/nodejs/' + config.API_LEVEL + '/otherdocs/dlPdf/' + $scope.documentId;
                var filename = otherdoc.name + '.pdf';

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


        $scope.goo = function () {
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
        };


        $rootScope.$broadcast('buttonDrawersVisibility',{visibility: true});


    });
})();
