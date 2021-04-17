(function () {
    angular.module('idelibreApp').controller('ModalPurge', function ($scope, $modalInstance, $rootScope) {



        $scope.confirm = function () {
            $rootScope.$broadcast("purge", {});
            $modalInstance.dismiss('cancel');
        };
        
        $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
        }

    });

})();