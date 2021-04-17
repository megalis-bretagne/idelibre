(function () {
    'use strict';

    angular.module('idelibreApp').directive('annotationDrawerItem', function ($rootScope) {
        return {
            templateUrl: 'js/directives/annotationDrawerItem.html',
            restrict: 'E',
            replace: false,
            scope: {
                annotation: '=',
                account: '=',
                action: '&'
            },
            controller: function ($scope) {
                $scope.annotation.formatedDate = DateUtils.formatedDateWithTime($scope.annotation.date);

                $scope.isSharedByMe = function () {
                    if ($scope.annotation.authorId !== $scope.account.userId)
                        return false;
                    return  $scope.annotation.sharedUserIdList.length > 0;
                }

                $scope.isNotShared = function () {
                    return  $scope.annotation.sharedUserIdList.length === 0;
                }


                $scope.isLocked = function () {
                    return $scope.annotation.authorId !== $scope.account.userId;
                }


                $scope.goToPage = function () {
                    $rootScope.$broadcast('goToPage', {
                        page: $scope.annotation.page
                    });
                };
            }
        };
    });
})();