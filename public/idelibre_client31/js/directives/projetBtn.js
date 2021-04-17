(function () {
    'use strict';
    angular.module('idelibreApp').directive('projetBtn', function ($location, $http, $rootScope, annotationSrv, localDbSrv, socketioSrv, accountSrv) {

        /**
         * @namespace projetBtn
         */
        return {
            templateUrl: 'js/directives/projetBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                data: '=',
                collectivite: '=',
                account: '=', //accountId
                seance: '=',
                state: '=',
                action: '&'
            },
            /**
             *
             * @param {type} $scope
             * @function projetBtn.controller
             */
            controller: function ($scope) {

                var account = accountSrv.findAccountById($scope.account);

                //TODO remove only for dev (put the state to connected !

                $scope.annotations = $scope.data.countAnnotations();

                $scope.isRapporteur = false;
                // TODO Vérifier si connecter avant de lancer un téléchargement



                if($scope.data.rapporteurId == account.userId){
                    $scope.isRapporteur = true;
                }


                /**
                 * redirige vers le document
                 * @returns {}
                 */
                var goToDocument = function () {
                    ///projet/:documentId/:projetId/:seanceId/:acountId
                    $location.path('/projet/' + $scope.data.document_text.id + '/' + $scope.data.id + '/' + $scope.seance + '/' + $scope.account);

                };


                var setAnnotationsToRead = function () {
                    if ($scope.annotations.unread > 0) {
                        _.each($scope.data.getAnnotations(), function (annotation) {
                            if (!annotation.isRead) {
                                annotation.isRead = true;
                                annotationSrv.addToReadList($scope.account, annotation.id);
                                socketioSrv.sendAnnotationRead($scope.account);
                                accountSrv.save();
                            }
                        });
                    }
                }


                $scope.$on("refreshAnnotations", function (event, mydatas) {
                    $scope.annotations = $scope.data.countAnnotations();
                    if (!$rootScope.$$phase) {
                        $scope.$apply();
                    }
                });



                /**
                 * action au click (telechargement ou ouverture)
                 * @returns {undefined}
                 */
                $scope.action = function () {
                    //si le doc est déja en cours de téléchargemtn on ne fait rien
                    if ($scope.data.document_text.isLoaded === PENDING) {
                        return;
                    }

                    //si le doc est chargé on l'ouvre
                    if ($scope.data.document_text.isLoaded) {
                        $scope.data.unreadAnnotation = false;
                        //setAnnotationRead
                        setAnnotationsToRead();
                        goToDocument();
                    }
                    //sinon on le le télécharge
                    else {
                        //si on a une connection
                        $scope.state = true;
                        if ($scope.state) {
                            $scope.loadingState = PENDING;
                            $scope.data.document_text.isLoaded = PENDING;

                            localDbSrv.getProjet($scope.data.document_text, null, $scope.seance, $scope.account);
                        }
                    }
                };


                $scope.loadingState = $scope.data.document_text.isLoaded;


                $scope.loadedProjetPercent = function () {
                    return Math.round(($scope.loadedProjetPart / $scope.countProjetPart) * 100);
                };






                //ecouteur pour la mise à jour du nombre de projet chargée
                var cleanup1 = $scope.$on('update loaded projet', function (event, data) {
                    if (data.documentId === $scope.data.document_text.id) {
                        $scope.loadingState = $scope.data.document_text.isLoaded;
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                //ecouteur pour la mise a jour du nombre de part chargé
                var cleanup2 = $scope.$on('update loaded projet pdfpart', function (event, data) {
                    if (data.documentId === $scope.data.document_text.id) {
                        $scope.loadedProjetPart = $scope.data.countProjetPdfdatasLoaded();
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                //si le téléchargement echoue
                var cleanup3 = $rootScope.$on('error loaded projet', function (event, data) {
                    if (data.documentId === $scope.data.document_text.id) {
                        $scope.data.document_text.isLoaded = NOTLOADED;
                        $scope.loadingState = LOAD_ERROR;
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });



                //nombre de partie à téléchargés (pas forcement enregistrée) NON AFFICHé à l'écran !
                $scope.loadedProjetPartdl = 0;
                var cleanup4 = $scope.$on('update loaded projet pdfpart dl', function (event, data) {
                    if (data.documentId === $scope.data.document_text.id) {
                        //  alert('toto');
                        $scope.loadedProjetPartdl += 1;
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                var cleanup5 = $scope.$on('make spin icon', function (event, data) {

                    if (data.documentId === $scope.data.document_text.id) {
                        //  alert('toto');
                        $scope.loadingState = PENDING;
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                var annotationItemListener = $scope.$on('refreshAnnotationItems', function (event, data) {

                    if (data.id == $scope.data.id) {
                        $scope.annotations = $scope.data.countAnnotations();
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


//refreshAnnotationItems'










                /**
                 * Choix de la classe pour l'icone détat de chargement
                 * return {string}
                 */
                $scope.chooseClassState = function () {
                    switch ($scope.loadingState) {
                        case NOTLOADED:
                            return "fa fa-arrow-down red_color fa-2x";
                            break;
                        case PENDING:
                            return "fa fa-spinner fa-spin fa-2x";
                            break
                        case LOADED:
                            return "fa fa-check green_color fa-2x";
                            break
                        case LOAD_ERROR:
                            return "fa fa-save red_color fa-2x";
                            break
                        default:
                            return 'bold';

                    }
                };


                $scope.$on('$destroy', function () {
                    cleanup1();
                    cleanup2();
                    cleanup3();
                    cleanup4();
                    cleanup5();
                    annotationItemListener();
                });


                /**
                 * redirige à l'ordre du jour de la seance
                 * @returns {}
                 */
                $scope.goToOdj = function () {
                    $location.path('/odj/' + $scope.data.id);
                };

            }

        };

    });

})();