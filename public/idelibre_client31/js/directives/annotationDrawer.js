(function () {
    'use strict';
    angular.module('idelibreApp').directive('annotationDrawer', function () {

        /**
         * @namespace accountBtn
         */
        return {
            templateUrl: 'js/directives/annotationDrawer.html',
            restrict: 'E',
            replace: false,
            scope: {
                annotations: '=',
                account:'=',
                action: '&'
            },
            /**
             * 
             * @param {type} $scope
             * @function accountBtn.controller
             */
            controller: function ($scope) {

            }
        }
    });
})();