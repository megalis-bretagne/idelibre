
(function () {
    'use strict';

    angular.module('idelibreApp').directive('navigationDrawerAnnexe', function ($rootScope, localDbSrv, $location, dlOriginalSrv) {
        return {
            templateUrl: 'js/directives/navigationDrawerAnnexe.html',
            restrict: 'E',
            replace: false,
            scope: {
                annexe: '=',
                seance: '=',
                account: '=',
                projet: '=',
                action: '&'
            },
            controller: function ($scope) {

                $scope.stateClass = "fa fa-arrow-down red_color";

                if ($scope.annexe.annexe_name.slice(-3) !== "pdf") {
                    $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";
                } else {
                    if ($scope.annexe.loaded === 2) {
                        $scope.stateClass = "fa fa-check green_color-lg";
                    } else if ($scope.annexe.loaded === 1) {
                        $scope.stateClass = "fa fa-spinner fa-spin fa-lg";

                    } else {
                        $scope.stateClass = "fa fa-arrow-down red_color";
                    }
                }


                $scope.clickOnAnnexe = function () {
                    //si il s'agit d'un document de type autre que pdf on fait un bete dl
                    if ($scope.annexe.annexe_name.slice(-3) !== "pdf") {
                        downloadAnnexe($scope.annexe.annexe_id);
                    }//sinon on télécharge le doc en locale
                    else {
                        //si pas chargé
                        if ($scope.annexe.loaded === 0) {
                            $scope.stateClass = "fa fa-spinner fa-spin fa-lg";
                            $scope.annexe.loaded = 1;

                            localDbSrv.addAnnexeToDownload($scope.account.name, $scope.seance, $scope.account.id, $scope.annexe);

                        }
                        //si l'annexe est chargée
                        if ($scope.annexe.loaded == 2) {
                            $location.path('/annexe/' + $scope.annexe.annexe_id + '/' + $scope.projet.id + '/' + $scope.seance.id + '/' + $scope.account.id);
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
                        var seance = $scope.account.findSeance($scope.seanceid);
                        var date = formatedDate(new Date(parseInt(seance.date)));
                        var url = $scope.account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + $scope.data.annexe_id;
                        dlOriginalSrv.dlPDF($scope.account, url, $scope.data.annexe_name, "mime", "idelibre/" + date, callbackSuccess, callbackError);
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



                $scope.$on('update loaded annexe', function (event, data) {
                    if (data.documentId == $scope.annexe.annexe_id) {
                        $scope.annexe.loaded = 2;
                        $scope.stateClass = "fa fa-check fa-lg green_color";
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                $scope.$on('error loaded annexe', function (event, data) {
                    if (data.documentId == $scope.annexe.annexe_id) {
                        $scope.annexe.loaded = 0;
                        $scope.stateClass = "fa fa-save fa-lg red_color";
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });

            }
        };
    });
})();