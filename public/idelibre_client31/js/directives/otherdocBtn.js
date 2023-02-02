(function () {
    'use strict';
    angular.module('idelibreApp').directive('otherdocBtn', function ($location, $http, $rootScope, annotationSrv, localDbSrv, socketioSrv, accountSrv) {

        /**
         * @namespace otherdocBtn
         */
        return {
            templateUrl: 'js/directives/otherdocBtn.html',
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
             * @function otherdocBtn.controller
             */
            controller: function ($scope) {

                var account = accountSrv.findAccountById($scope.account);

                /**
                 * redirige vers le document
                 * @returns {}
                 */
                var goToDocument = function () {
                    $location.path('/otherdoc/' + $scope.data.document_text.id + '/' + $scope.data.id + '/' + $scope.seance + '/' + $scope.account);
                };

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


                $scope.loadedOtherdocPercent = function () {
                    return Math.round(($scope.loadedOtherdocPart / $scope.countOtherdocPart) * 100);
                };


                //ecouteur pour la mise à jour du nombre de otherdoc chargée
                var cleanup1 = $scope.$on('update loaded projet', function (event, data) {
                    if (data.documentId === $scope.data.document_text.id) {
                        $scope.loadingState = $scope.data.document_text.isLoaded;
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


                var cleanup5 = $scope.$on('make spin icon', function (event, data) {

                    if (data.documentId === $scope.data.document_text.id) {
                        //  alert('toto');
                        $scope.loadingState = PENDING;
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });
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
                    cleanup3();
                    cleanup5();
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