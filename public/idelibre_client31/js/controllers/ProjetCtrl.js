(function () {
    'use strict';

    angular.module('idelibreApp').controller('ProjetCtrl', function ($rootScope, $scope, $routeParams, $location, dlOriginalSrv, usSpinnerService, $timeout, fakeUrlSrv, workerSrv, accountSrv) {

        workerSrv.clearDocument();

        fakeUrlSrv.removeUrls();
       
        $scope.accountId = $routeParams.acountId;
        $scope.seanceId = $routeParams.seanceId;
        $scope.documentId = $routeParams.documentId;
        $scope.projetId = $routeParams.projetId;

        var account = accountSrv.findAccountById($scope.accountId);
        var seance = account.findSeance($scope.seanceId);
        var projet = seance.findProjet($scope.projetId);
        
        $scope.seance = seance;
        $scope.account = account;
        $scope.document = projet;

        // on envoie le nom du document
        $rootScope.$broadcast('name', {
            name: projet.rank + 1 + "- " + projet.name
        });

        $scope.toggleSearchbar = function () {
            $rootScope.$broadcast('toggle searchbar');
        };


        $scope.goToOdj = function () {
            $rootScope.$broadcast('close searchbar');
            $location.path('/odj/' + $scope.seanceId + '/' + $scope.accountId);
        };

        
//
//        $scope.$on("$destroy", function () {
//            container = null;
//
//            $('ul.dropdown-menu').off("click");
//////            dropdown = null;
//
//            myPdfDocument.destroy();
//            myPdfDocument.cleanup();
//            pdfLinkService.setDocument(null, null);
//            pdfLinkService = null;
//            pdfViewer.cleanup();
//            pdfViewer.setDocument(null);
//            $(document).unbind('saveAnnot');
//            $(document).unbind('deleteAnnot');
//            findController.deleteListener();
//            findController.setFindBar(null);
//            findBar.deleteListeners();
//            findBar = null;
//            findController = null;
//        });




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
