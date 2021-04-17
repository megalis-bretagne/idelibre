(function () {
    'use strict';

    angular.module('idelibreApp').directive('navigationDrawerItem', function ($location, localDbSrv, $rootScope) {
        return {
            templateUrl: 'js/directives/navigationDrawerItem.html',
            restrict: 'E',
            replace: false,
            scope: {
                projet: '=',
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
                    switch ($scope.projet.document_text.isLoaded) {
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

                var cleanup1 = $scope.$on('update loaded projet', function (event, data) {
                    if (data.documentId === $scope.projet.document_text.id) {
                        $scope.loadingState = $scope.projet.document_text.isLoaded;
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });





                $scope.clickOnProjet = function (e) {
                    if ($scope.projet.document_text.isLoaded == LOADED) {
                        $location.path('/projet/' + $scope.projet.document_text.id + '/' + $scope.projet.id + '/' + $scope.seance.id + '/' + $scope.account.id);
                    }
                    if ($scope.projet.document_text.isLoaded == NOTLOADED || $scope.projet.document_text.isLoaded == LOAD_ERROR) {
                        
                        $scope.loadingState = PENDING;
                        $scope.projet.document_text.isLoaded = PENDING;
                        localDbSrv.getProjet($scope.projet.document_text, null, $scope.seance.id, $scope.account.id);
                    }
                };

                $scope.toggleAnnexe = function (e) {
                    e.stopPropagation();
                    $scope.projet.showAnnexe = !$scope.projet.showAnnexe;
                }
            }
        };
    });
})();