(function () {
    'use strict';

    angular.module('idelibreApp').directive('navigationDrawerOtherdoc', function ($location, localDbSrv, $rootScope) {
        return {
            templateUrl: 'js/directives/navigationDrawerOtherdoc.html',
            restrict: 'E',
            replace: false,
            scope: {
                otherdoc: '=',
                seance: '=',
                account: '=',
                action: '&'
            },
            controller: function ($scope) {

                /**
                 * Choix de la classe pour l'icone détat de chargement
                 * return {string}
                 */
                $scope.chooseClassState = function () {
                    switch ($scope.otherdoc.document_text.isLoaded) {
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


                $scope.clickOnOtherdoc = function (e) {
                    if ($scope.otherdoc.document_text.isLoaded == LOADED) {
                        $location.path('/otherdoc/' + $scope.otherdoc.document_text.id + '/' + $scope.otherdoc.id + '/' + $scope.seance.id + '/' + $scope.account.id);
                    }
                    if ($scope.otherdoc.document_text.isLoaded == NOTLOADED || $scope.otherdoc.document_text.isLoaded == LOAD_ERROR) {
                        
                        $scope.loadingState = PENDING;
                        $scope.otherdoc.document_text.isLoaded = PENDING;
                        localDbSrv.getProjet($scope.otherdoc.document_text, null, $scope.seance.id, $scope.account.id);
                    }
                };

            }
        };
    });
})();