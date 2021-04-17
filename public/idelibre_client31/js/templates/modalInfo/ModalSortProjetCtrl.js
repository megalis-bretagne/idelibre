(function () {
    angular.module('idelibreApp').controller('ModalSortProjetCtrl', function ($scope, $modalInstance, $rootScope) {


        $scope.ordre = function(){
           $rootScope.$broadcast('sortSeanceBy', {sortBy: "rank"});
           $modalInstance.dismiss('cancel');
        };
        
        $scope.nom = function(){
            $rootScope.$broadcast('sortSeanceBy', {sortBy: "name"});
            $modalInstance.dismiss('cancel');
        };
        
        $scope.theme = function(){
            $rootScope.$broadcast('sortSeanceBy', {sortBy: "theme"});
            $modalInstance.dismiss('cancel');
        };


        $scope.ok = function () {
            $modalInstance.dismiss('cancel');
        };

    });

})();