/*
 * Copyright (c) 2018. Libriciel scop
 * i-delibRE 3.1
 * LICENCE CeCILL v2
 *
 */

(function () {
    angular.module('idelibreApp').controller('ModalConfirmAbsentCtrl', function ($scope, account, seance, $rootScope, $modalInstance, socketioSrv, accountSrv) {

        $scope.procName = {name: ""};

        $scope.simpleConfirm = function(){
            seance.setPresentStatus(Seance.ABSENT);
            accountSrv.save();
            socketioSrv.sendConfirmPresence(account, seance.id, Seance.ABSENT, null);
            $modalInstance.dismiss('cancel');
        }


        $scope.confirmWithName = function(){
            console.log("conf");
            console.log($scope.procName.name);
            if($scope.procName.name && $scope.procName.name !=""){
                seance.setPresentStatus(Seance.ABSENT);
                accountSrv.save();
                socketioSrv.sendConfirmPresence(account, seance.id, Seance.ABSENT, $scope.procName.name );
                $modalInstance.dismiss('cancel');
            }
        }

        $scope.cancel = function(){
            $modalInstance.dismiss('cancel');
        }


    });

})();
