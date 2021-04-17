(function () {
    'use strict';

    angular.module('idelibreApp').controller('AnnexeCtrl', function ($scope, $rootScope, fakeUrlSrv, $routeParams, $location, $log, localDbSrv, accountSrv, dlOriginalSrv) {


        $rootScope.$broadcast('buttonDrawersVisibility', {visibility: true});
        
        var annexeId = $routeParams.annexeId;
        var seanceId = $routeParams.seanceId;
        var accountId = $routeParams.accountId;
        var projetId = $routeParams.projetId;

        //recuperation de l'accout, de la seance, du projet et de l'annexe
        var account = accountSrv.findAccountById(accountId);
        var seance = account.findSeance(seanceId);
        var projet = seance.findProjet(projetId);
        var annexe = projet.findAnnexe(annexeId);

        $scope.users = seance.getSharedUsers(account.userId);

        $scope.seance = seance;
        $scope.account = account;
        $scope.document = annexe;
        $scope.documentId = annexe.annexe_id;

        // on envoie le nom de la page
        $rootScope.$broadcast('name', {
            name: annexe.name
        });

        $scope.goToOdj = function () {
            $location.path('/odj/' + seanceId + '/' + accountId);
        };
        $scope.goToAccount = function () {
            $location.path('/seance/' + accountId);
        };
        $scope.goToProjet = function () {
            var documentId = projet.document_text.id;
            $location.path('/projet/' + documentId + '/' + projetId + '/' + seanceId + '/' + accountId);
        };


        $scope.downloadAnnexe = function () {
            var date = seance.date.split(' ')[0];

            dlOriginalSrv.dlPDF(loginSrv.full_url + '/idelibre21/nodejs/getAnnexe/' + annexeId + '/' + account.name, annexe.name + '.pdf', 'application/pdf', "idelibre/" + date);
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


        $scope.downloadAnnexe = function () {
            if ($scope.dlStatus === false) {
                $scope.dlStatus = true;
                $scope.stateClass = "fa fa-spinner fa-spin fa-lg ";
                //var date = formatedDate(new Date(parseInt(seance.date)));
                var date = DateUtils.formatedDate(seance.date);
                var url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + annexeId;
                dlOriginalSrv.dlPDF(account, url, annexe.annexe_name, "mime", "idelibre/" + date, callbackSuccess, callbackError);
            }
        };

        $scope.goo = function () {
            $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
        };


    });
})();
