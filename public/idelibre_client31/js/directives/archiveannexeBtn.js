(function () {
    'use strict';

    angular.module('idelibreApp').directive('archiveannexeBtn', function ($location, $rootScope, accountSrv, dlOriginalSrv) {


        /**
         * @namespace seanceBtn
         */
        return {
            templateUrl: 'js/directives/archiveAnnexeBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                annexe: '=',
                projetid: '=',
                accountid: '=',
                seanceid: '=',
                state: '='
            },
            /**
             * 
             * @param {type} $scope
             * @function seanceBtn.controller
             */
            controller: function ($scope) {

                var annexeId = $scope.annexe.annexe_id;
                var projetId = $scope.projetid;
                var account = accountSrv.findAccountById($scope.accountid);


                $scope.name = $scope.annexe.annexe_name;
                $scope.stateClass = "fa fa-save";

                if ($scope.annexe.annexe_type !== "application/pdf") {
                    $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";
                }

                $scope.action = function () {
                    if (account.status == ONLINE) {
                        //si il s'agit d'un document de type autre que pdf on fait un bete dl
                        if ($scope.annexe.annexe_type !== "application/pdf") {
                            downloadAnnexe();
                        } else {
                            $location.path('/archiveAnnexe/' + annexeId + '/' + projetId + '/' + $scope.seanceid + '/' + $scope.accountid);
                        }
                    }
                };


                var callbackSuccess = function () {
                    $scope.dlStatus = false;
                    $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";
                    $rootScope.$broadcast('modalOpen', {title: 'Téléchargement terminé', content: 'Votre document a bien été téléchargé'});
                }


                var callbackError = function () {
                    $scope.dlStatus = false;
                    $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";
                    $rootScope.$broadcast('modalOpen', {title: 'Téléchargement erreur', content: 'Votre document n\'a pas été téléchargé'});
                };


                $scope.dlStatus = false;
                //Téléchargement du document
                var downloadAnnexe = function () {
                    if ($scope.dlStatus === false) {
                        $scope.dlStatus = true;
                        $scope.stateClass = "fa fa-spinner fa-spin fa-lg ";
                        var url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + $scope.annexe.annexe_id;
                        dlOriginalSrv.dlPDF(account, url, $scope.name, "mime", "idelibre/" + "archived", callbackSuccess, callbackError);
                    }
                };

            }
        };

    });

})();


