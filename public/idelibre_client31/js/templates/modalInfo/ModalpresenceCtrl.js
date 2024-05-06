(function () {
    angular.module('idelibreApp').controller('ModalpresenceCtrl', function ($scope, account, seance, attendance, $rootScope, $modalInstance, socketioSrv, accountSrv, attendanceSrv) {

        let isShowMandators = false;

        if(account.type === ACTEURS){
            isShowMandators = true;
        }


        $scope.isAllowedRemote = seance.isRemoteAllowed

        $scope.isPresenceStatusEditable = seance.getDate() > Date.now();

        $scope.attendance = attendance;

        $scope.isMandatorList = false
        $scope.selectedMandator = null;
        $scope.availableMandators = attendance.availableMandators;

        $scope.isMandatorAllowed = attendance.isMandatorAllowed && isShowMandators;


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
            $modalInstance.close();
        }

        let callBackError = function () {
            $modalInstance.dismiss('cancel');
        }


        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.present = function () {
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.PRESENT}, callBackSuccess, callBackError);
        };

        $scope.presentRemotely = function () {
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.REMOTE}, callBackSuccess, callBackError);
        }

        $scope.absent = function () {
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.ABSENT}, callBackSuccess, callBackError);
        };

        $scope.absentMandator = function () {
            $scope.isMandatorList = true
        }

        $scope.chooseMandator = function () {
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.POA, mandatorId: $scope.selectedMandator}, callBackSuccess, callBackError);
        }

        $scope.absentDeputy = function () {
            attendanceSrv.postAttendanceStatus(account, seance.id, {attendance: Seance.DEPUTY, mandatorId: null}, callBackSuccess, callBackError);
        }
    });

})();
