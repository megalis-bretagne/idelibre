(function () {


    angular.module('idelibreApp').directive('footbar', function () {

        return {
            templateUrl: 'js/directives/footbar.html',
            restrict: 'E',
            replace: false,
            
            controller: function ($scope) {

                $scope.version = idelibreConf.version;

            }
        };



    });

})();



