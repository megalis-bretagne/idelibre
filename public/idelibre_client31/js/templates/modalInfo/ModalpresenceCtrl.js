(function () {
    angular.module('idelibreApp').controller('ModalpresenceCtrl', function ($scope, account, seance, attendance, $rootScope, $modalInstance, socketioSrv, accountSrv, attendanceSrv) {


        console.log(attendance);

        $scope.isAllowedRemote = seance.isRemoteAllowed

        $scope.isPresenceStatusEditable = seance.getDate() > Date.now();

        $scope.attendance = attendance;
        //$scope.presenceStatus = seance.getPresentStatus();

        $scope.isMandatorList = false
        $scope.selectedMandator = null;
        $scope.availableMandators = attendance.availableMandators;


        $scope.getPresenceMessageEditable = () => {
            switch (attendance.attendance) {
                case Seance.ABSENT :
                    return "Vous êtes enregistré absent"
                case Seance.PRESENT :
                    return "Vous êtes enregistré présent"
                case Seance.REMOTE :
                    return "Vous êtes enregistré présent à distance"
                case Seance.DEPUTY :
                    return "Vous avez préciser etre remplacé pour votre suppléant"
                case Seance.POA :
                    return "Vous êtes enregistré avoir donner pouvoir a un mandataire";

                case Seance.undefined :
                    return "Merci de renseigner votre présence"
                default :
                    return "Merci de renseigner votre présence"
            }
        }

        let callBackSuccess = function () {
            console.log('success');
            $modalInstance.close();
        }

        let callBackError = function () {
            console.log('error');
            $modalInstance.dismiss('cancel');
        }


        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.present = function () {
            //seance.setPresentStatus(Seance.PRESENT);
            //  accountSrv.save();
            // socketioSrv.sendConfirmPresence(account, seance.id, Seance.PRESENT, null);
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.PRESENT}, callBackSuccess, callBackError);
            //$modalInstance.close();
            //$modalInstance.dismiss('cancel');
        };

        $scope.presentRemotely = function () {
            //  seance.setPresentStatus(Seance.REMOTE);
            //  accountSrv.save();
            //socketioSrv.sendConfirmPresence(account, seance.id, Seance.REMOTE, null);
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.REMOTE}, callBackSuccess, callBackError);
            //$modalInstance.dismiss('cancel');
            //$modalInstance.close();
        }

        $scope.absent = function () {
            // seance.setPresentStatus(Seance.ABSENT);
            // accountSrv.save();
            //socketioSrv.sendConfirmPresence(account, seance.id, Seance.ABSENT);

            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.ABSENT}, callBackSuccess, callBackError);
            //$modalInstance.close();
            //            $modalInstance.dismiss('cancel');
            //} else {
            //     $modalInstance.close(Seance.ABSENT);
            // }
        };

        $scope.absentMandator = function () {
            $scope.isMandatorList = true
        }

        $scope.chooseMandator = function () {
            console.log($scope.selectedMandator);
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.POA, mandatorId: $scope.selectedMandator}, callBackSuccess, callBackError);
            //$modalInstance.close();
            //$modalInstance.dismiss('cancel');
        }

        $scope.absentDeputy = function () {
            console.log($scope.selectedMandator);
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.DEPUTY, mandatorId: null}, callBackSuccess, callBackError);

            //$modalInstance.dismiss('cancel');
        }
    });

})();
