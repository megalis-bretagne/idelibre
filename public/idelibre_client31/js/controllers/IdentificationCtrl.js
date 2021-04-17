(function () {
    'use strict';

    angular.module('idelibreApp').controller('IdentificationCtrl', function ($scope, accountSrv, $location, localDbSrv, $modal) {


        $scope.cordova = config.cordova;
        $scope.accounts = accountSrv.getList();

        $scope.addAccount = function () {

            var accountToAdd = new Account();
            accountToAdd.id = guid();
            accountToAdd.username = "";
            accountToAdd.password = "";
            accountToAdd.suffix = "";
            accountToAdd.name = "";

            accountSrv.add(accountToAdd);
        };


        //UUID GENERATOR
        function guid() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                        .toString(16)
                        .substring(1);
            }
            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                    s4() + '-' + s4() + s4() + s4();
        }


        $scope.goToAccueil = function () {
            $location.path('/accueil');
        };

        $scope.purgeData = function () {
           // localDbSrv.purge();
            popupConfirm();

        }


        var popupConfirm = function () {
            $modal.open({
                templateUrl: 'js/templates/modalInfo/ModalPurge.html',
                controller: 'ModalPurge',
                size: 'sm',
            });
        };

        
        $scope.$on("purge", function(){
            localDbSrv.purge();
        });
        


    });


})();











