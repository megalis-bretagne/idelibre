(function () {
    angular.module('idelibreApp').controller('ModalpresenceCtrl', function ($scope, account, seance, $rootScope, $modalInstance, socketioSrv, accountSrv) {

        var maDate = Date.now();
        $scope.presenceStatus = 'undefined';
        if( maDate > seance.getDate() ) {
            $scope.presenceStatus = seance.getPresentStatus();
        }

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.present = function () {
            if(seance.getIsRemoteStatus() === true) {
                if(account.type != ACTEURS) {
                    seance.setPresentStatus(Seance.PRESENT);
                    accountSrv.save();
                    socketioSrv.sendConfirmPresence(account, seance.id, Seance.PRESENT, null);
                    $modalInstance.dismiss('cancel');
                }else {
                    $modalInstance.close(Seance.PRESENT);
                }
            }
            else {
                seance.setPresentStatus(Seance.PRESENT);
                accountSrv.save();
                socketioSrv.sendConfirmPresence(account, seance.id, Seance.PRESENT, null);
                $modalInstance.dismiss('cancel');
            }

        };

        $scope.absent = function () {
            if(account.type != ACTEURS) {
                seance.setPresentStatus(Seance.ABSENT);
                accountSrv.save();
                socketioSrv.sendConfirmPresence(account, seance.id, Seance.ABSENT);
                $modalInstance.dismiss('cancel');
            }else {
                $modalInstance.close(Seance.ABSENT);
            }
        };
    });

})();
