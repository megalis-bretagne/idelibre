(function () {
    'use strict';

    angular.module('idelibreApp').directive('archiveBtn', function ($location) {


        /**
         * @namespace seanceBtn
         */
        return {
            templateUrl: 'js/directives/archiveBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                accountid: '=',
                state: '='
            },
            /**
             * 
             * @param {type} $scope
             * @function seanceBtn.controller
             */
            controller: function ($scope) {

                $scope.action = function () {
                        $location.path('/archive/' + $scope.accountid);
                };

            }

        };

    });

})();