(function () {
    'use strict';

    angular.module('idelibreApp').directive('navigationDrawer', function ($rootScope, $timeout) {
        return {
            templateUrl: 'js/directives/navigationDrawer.html',
            restrict: 'E',
            replace: false,
            scope: {
                seance: '=',
                account: '=',
                currentdocument: '=',
                action: '&'
            },
            controller: function ($scope) {
                //for each projet showAnnexe = false !
                $scope.seance.projets.forEach(function(projet){
                    projet.showAnnexe = false;
                });


                $scope.$on('toggleLeftDrawer', function (e, data) {
                    //find id


                    $timeout(function () {

                        var anchorId =  "anchor"+ $scope.currentdocument.id;
                        var anchor = document.getElementById(anchorId);
                        var offsetY = anchor.offsetTop;
                        var ld = document.getElementById("navigationDrawer");
                        ld.scrollTop = offsetY;
                    }, 0);

                });

            }
        };
    });
})();