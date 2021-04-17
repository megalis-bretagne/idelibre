(function () {
    'use strict';

    angular.module('idelibreApp').controller('SeanceCtrl', function ($scope, $rootScope, $routeParams, $location, $log, $modal, loginSrv, socketioSrv, accountSrv, arSrv) {

        $rootScope.$broadcast('buttonDrawersVisibility',{visibility: false});

        $scope.accountId = $routeParams.accountId;

        var findAccount = function () {
            var account = _.find(accountSrv.getList(), function (account) {
                return account.id === $scope.accountId;
            });
            return account;
        };

        $scope.account = findAccount();

        var connected = function () {
            return $scope.account.status == ONLINE;
        };

        $scope.connectionStatus = connected();

        $scope.$on('connectionStatus', function (event, data) {
            $scope.connectionStatus = connected();
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }
        });


        // envoie le nom de la page pour la navbar
        $rootScope.$broadcast('name', {
            name: 'collectivité : ' + $scope.account.name
        });


        // pour le modal dans le cas ou la convocation est non lue
        $scope.open = function (size, data) {

            var modalInstance = $modal.open({
                templateUrl: 'ARModal.html',
                controller: 'ModalInstanceCtrl',
                size: size
            });

            modalInstance.result.then(function (selectedItem) {
                // on note la convocation comme lu
                if(data.seance.convocation) {
                    data.seance.convocation.isRead = true;
                }
                else if(data.seance.invitation) {
                    data.seance.invitation.isRead = true;
                }

                //on transmet une action de lecture de la convocataion pour le serveur ou le todo si non connecté
                var arData = {
                    accountId: data.accountId,
                    seanceId: data.seance.id
                };
                arSrv.add(arData);
                arSrv.save();
                socketioSrv.sendAR();
                accountSrv.save();
                $location.path('/odj/' + data.seance.id + '/' + data.accountId);
            }, function () {

            });
        };





        $scope.$on('ar', function (event, data) {
            $scope.open('lg', data);
        });


        $scope.goToAccueil = function () {
            $location.path('/accueil');
        };


        $scope.account.seances = _.sortBy($scope.account.seances, function (seance) {
            return seance.date;
        });

    });


    //TODO put it in it own controller file !
    angular.module('idelibreApp').controller('ModalInstanceCtrl', function ($scope, $modalInstance) {

        $scope.ok = function () {
            $modalInstance.close();
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

    });

})();

