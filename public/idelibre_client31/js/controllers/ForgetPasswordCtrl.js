(function () {
    'use strict';

    angular.module('idelibreApp').controller('ForgetPasswordCtrl', function ($scope, accountSrv, $location, $rootScope, $http) {
        $scope.account = {};

        $scope.reInit = function () {
            console.log($scope.account);
            if (!$scope.account.username || !$scope.account.suffix) {
                $rootScope.$broadcast('notify', {class: 'danger', content: "Tous les champs doivent ếtre rensignés"});
            }

            $http.post( '/nodejs/forgetPassword', $scope.account)

                .success(function (data, status, headers, config) {
                    console.log(data, status);
                    $rootScope.$broadcast('notify', {class: 'success', content: "Vous allez recevoir un email si ce compte existe"});
                })
                .error(function (data, status, headers, config) {
                    $rootScope.$broadcast('notify', {class: 'danger', content: "Erreur lors de la demande de mot de passe oublié"});
                });

        };

    });


})();











