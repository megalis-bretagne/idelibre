(function () {
    'use strict';

    angular.module('idelibreApp').directive('annexeBtn', function ($location, $rootScope, localDbSrv, dlOriginalSrv, accountSrv, annotationSrv, socketioSrv) {


        /**
         * @namespace seanceBtn
         */
        return {
            templateUrl: 'js/directives/annexeBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                data: '=',
                collectivite: '=',
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


                $scope.annotations = $scope.data.countAnnotations();




                var account = accountSrv.findAccountById($scope.accountid);
                var seance = account.findSeance($scope.seanceid);
                // état de chargement de l'annexe
                $scope.stateClass = "fa fa-arrow-down red_color";

                if ($scope.data.annexe_name.slice(-3) !== "pdf") {
                    $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";

                } else {
                    if ($scope.data.loaded === 2) {
                        $scope.stateClass = "fa fa-check green_color-lg";
                    } else if ($scope.data.loaded === 1) {
                        $scope.stateClass = "fa fa-spinner fa-spin fa-lg";

                    } else {
                        $scope.stateClass = "fa fa-arrow-down red_color";
                    }

                }


                var setAnnotationsToRead = function () {
                    if ($scope.annotations.unread > 0) {
                        _.each($scope.data.getAnnotations(), function (annotation) {
                            if (!annotation.isRead) {
                                annotation.isRead = true;
                                annotationSrv.addToReadList($scope.accountid, annotation.id);
                                socketioSrv.sendAnnotationRead($scope.accountid);
                                accountSrv.save();
                            }
                        });
                    }
                }



                $scope.action = function () {
                    //si il s'agit d'un document de type autre que pdf on fait un bete dl
                    if ($scope.data.annexe_name.slice(-3) !== "pdf") {
                        //si non connecté on ne lance pas le dl
                        if (account.status != ONLINE) {
                            return;
                        }
                        //si on a une connexion le le charge
                        downloadAnnexe($scope.data.id);

                    }//sinon on télécharge le doc en locale
                    else {
                        //si pas chargé
                        if ($scope.data.loaded === 0) {
                            //si connecté
                            if (account.status == ONLINE) {
                                $scope.stateClass = "fa fa-spinner fa-spin fa-lg";
                                $scope.data.loaded = 1;

                                //si le téléchargement et l'enregistrement de l'annexe dans la bdd se passe bien
                                var callbackSuccessAnnexe = function () {
                                    $scope.data.loaded = 2;
                                    $scope.stateClass = "fa fa-check fa-lg green_color";
                                    if (!$rootScope.$$phase) {
                                        $scope.$apply();
                                    }
                                };

                                var callbackErrorAnnexe = function () {
                                    $scope.data.loaded = 0;
                                    $scope.stateClass = "fa fa-save fa-lg red_color";
                                    if (!$rootScope.$$phase) {
                                        $scope.$apply();
                                    }
                                };
                                localDbSrv.addAnnexeToDownload(account.name, seance, account.id, $scope.data);
                            }
                        }
                        //si l'annexe est chargée
                        if ($scope.data.loaded == 2) {
                            setAnnotationsToRead();
                            $location.path('/annexe/' + $scope.data.annexe_id + '/' + $scope.projetid + '/' + $scope.seanceid + '/' + $scope.accountid);
                        }
                    }
                };


                var callbackLoadedAnnexe = function () {
                    $scope.data.loaded = 2;
                    if (!$rootScope.$$phase) {
                        $scope.$apply();
                    }
                }



                var annotationItemListener = $scope.$on('refreshAnnotationItems', function (event, data) {
                    if (data.id == $scope.data.annexe_id) {
                        $scope.annotations = $scope.data.countAnnotations();
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });



                $scope.$on("refreshAnnotations", function (event, mydatas) {
                    $scope.annotations = $scope.data.countAnnotations();
                    if (!$rootScope.$$phase) {
                        $scope.$apply();
                    }
                });


                $scope.$on('update loaded annexe', function (event, data) {

                    if (data.documentId == $scope.data.annexe_id) {
                        $scope.data.loaded = 2;
                        $scope.stateClass = "fa fa-check fa-lg green_color";
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                $scope.$on('error loaded annexe', function (event, data) {
                    if (data.documentId == $scope.data.annexe_id) {
                        $scope.data.loaded = 0;
                        $scope.stateClass = "fa fa-save fa-lg red_color";
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


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

                var downloadAnnexe = function () {
                    if ($scope.dlStatus === false) {
                        $scope.dlStatus = true;
                        $scope.stateClass = "fa fa-spinner fa-spin fa-lg ";
                        var seance = account.findSeance($scope.seanceid);
                        var date = DateUtils.formatedDate(seance.date);
                        var url = account.url + "/nodejs/" + config.API_LEVEL + "/annexes/dlAnnexe/" + $scope.data.annexe_id;
                        dlOriginalSrv.dlPDF(account, url, $scope.data.annexe_name, "mime", "idelibre/" + date, callbackSuccess, callbackError);
                    }
                };

            }
        };
    });

})();
