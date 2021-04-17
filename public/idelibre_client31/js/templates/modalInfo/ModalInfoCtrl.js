(function () {
    angular.module('idelibreApp').controller('ModalInfoCtrl', function ($scope, $modalInstance, content, title, about) {


        $scope.content = content;
        $scope.title = title;
        $scope.about = about;



        $scope.ok = function () {
            $modalInstance.dismiss('cancel');

        };

    });

})();