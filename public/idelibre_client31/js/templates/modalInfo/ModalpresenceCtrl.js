(function () {
    angular.module('idelibreApp').controller('ModalpresenceCtrl', function ($scope, account, seance, $rootScope, $modalInstance, socketioSrv, accountSrv) {

        $scope.isAllowedRemote = seance.isRemoteAllowed

        $scope.isPresenceStatusEditable = seance.getDate() > Date.now();


        $scope.presenceStatus = seance.getPresentStatus();

        $scope.getPresenceMessageEditable = () => {
            switch (seance.getPresentStatus()) {
                case Seance.ABSENT : return "Vous êtes enregistré absent" + ( !!seance.deputy ? ". Vous avez donné pouvoir à : " + seance.deputy : "")
                case Seance.PRESENT : return "Vous êtes enregistré présent"
                case Seance.REMOTE : return "Vous êtes enregistré présent à distance"
                case Seance.undefined : return "Merci de renseigner votre présence"
                default : return "Merci de renseigner votre présence"
            }
        }

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.present = function () {
            seance.setPresentStatus(Seance.PRESENT);
            accountSrv.save();
            socketioSrv.sendConfirmPresence(account, seance.id, Seance.PRESENT, null);
            $modalInstance.dismiss('cancel');
        };

        $scope.presentRemotely = function () {
            seance.setPresentStatus(Seance.REMOTE);
            accountSrv.save();
            socketioSrv.sendConfirmPresence(account, seance.id, Seance.REMOTE, null);
            $modalInstance.dismiss('cancel');
        }

        $scope.absent = function () {
            if (account.type !== ACTEURS) {
                seance.setPresentStatus(Seance.ABSENT);
                accountSrv.save();
                socketioSrv.sendConfirmPresence(account, seance.id, Seance.ABSENT);
                $modalInstance.dismiss('cancel');
            } else {
                $modalInstance.close(Seance.ABSENT);
            }
        };
    });

})();
