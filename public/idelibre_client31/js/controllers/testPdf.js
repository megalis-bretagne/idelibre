/*
 * Copyright (c) 2019. Libriciel scop
 * i-delibRE 3.1
 * LICENCE CeCILL v2
 *
 */

(function () {
    'use strict';

    angular.module('idelibreApp').controller('ProjetCtrl', function ($rootScope, $scope, $routeParams, $location, dlOriginalSrv, usSpinnerService, $timeout, fakeUrlSrv, workerSrv, accountSrv) {

        workerSrv.clearDocument();

        fakeUrlSrv.removeUrls();
       


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
                var url = account.url + '/nodejs/' + config.API_LEVEL + '/projets/dlPdf/' + $scope.documentId;
                var filename = projet.name + '.pdf';

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
