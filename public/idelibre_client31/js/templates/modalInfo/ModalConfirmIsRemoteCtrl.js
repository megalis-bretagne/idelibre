/*
 * Copyright (c) 2018. Libriciel scop
 * i-delibRE 3.1
 * LICENCE CeCILL v2
 *
 */

(function () {
    angular.module('idelibreApp').controller('ModalConfirmIsRemoteCtrl', function ($scope, account, seance, $rootScope, $modalInstance, socketioSrv, accountSrv) {

        $scope.isRemoteStatus = {value: ""};
        $scope.confirmIsRemote = function(){
            seance.setPresentStatus(Seance.PRESENT);
            accountSrv.save();
            socketioSrv.sendConfirmPresence(account, seance.id, Seance.PRESENT, null, $scope.isRemoteStatus.value);
            $modalInstance.dismiss('cancel');
        }

        $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
        }


    });

})();
