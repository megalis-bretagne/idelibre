/**
 * 
 * data est un account
 * @returns {undefined}
 */
(function () {
    'use strict';
    angular.module('idelibreApp').directive('accountBtn', function ($location, $rootScope) {

        /**
         * @namespace accountBtn
         */
        return {
            templateUrl: 'js/directives/accountBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                data: '=',
                action: '&'
            },
            /**
             * 
             * @param {type} $scope
             * @function accountBtn.controller
             */
            controller: function ($scope) {
                /**
                 * rafraichit l'affichage si changement
                 */
                $scope.$on('refresh', function (event, data) {
                    if (data.accountId && data.accountId === $scope.data.id) {

                        $scope.countUnreadConvocation = $scope.data.countUnreadConvocation();
                        $scope.countSeances = $scope.data.countSeances();
                        $scope.countModifiedSeances = $scope.data.countModifiedSeances();

                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                $scope.$on("refreshAnnotations", function (event, data) {
                    if (!$rootScope.$$phase) {
                        $scope.$apply();
                    }

                });


                $scope.status = $scope.data.status;

                $scope.$on('connectionStatus', function (event, data) {
                    $scope.status = $scope.data.status;
                    if (!$rootScope.$$phase) {
                        $scope.$apply();
                    }
                });

                /**
                 * action du clique (aller à la liste des seances correspondantes )
                 * @returns {}
                 */
                $scope.goToSeancesList = function () {
                    $location.path('/seance/' + $scope.data.id);
                };


                //nombre de convocation non lu
                $scope.countUnreadConvocation = $scope.data.countUnreadConvocation();

                //nombre de projet chargé
                $scope.countLoadedProjets = $scope.data.countLoadedProjets();

                //nombre de seance
                $scope.countSeances = $scope.data.countSeances();

                //nombre de convocation chargée
                $scope.countLoadedConvocations = $scope.data.countLoadedConvocationDocument();

                //nombre de projet
                $scope.countProjets = $scope.data.countProjets();

                //nom du compte
                $scope.accountName = $scope.data.name;

                //nombre de seance modifiée
                $scope.countModifiedSeances = $scope.data.countModifiedSeances();

            }

        };

    });

})();