/*
 * Copyright (c) 2018. Libriciel scop
 * i-delibRE 3.1
 * LICENCE CeCILL v2
 *
 */

(function () {
    angular.module('idelibreApp').controller('ModalPasswordCtrl', function ($scope, account, $rootScope, $modalInstance, accountSrv, $http, socketioSrv) {

        $scope.isVisiblePassword = false
        $scope.togglePasswordClass = 'fa fa-eye fa-lg'

        $scope.togglePassword = () => {
            $scope.isVisiblePassword = !$scope.isVisiblePassword
            if(!$scope.isVisiblePassword) {
                $scope.togglePasswordClass = 'fa fa-eye fa-lg'
            }
            else {
                $scope.togglePasswordClass = 'fa fa-eye-slash fa-lg'
            }
        }

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.password = {};

        $scope.error = "";


        $scope.confirm = function () {
            console.log($scope.password);

            if ($scope.password.new != $scope.password.confirm) {
                console.log("password doesn't match");
                $scope.error = "Vos mots de passe ne correspondent pas"
                return;
            }

            if ($scope.password.old == "" || !$scope.password.old) {
                console.log("enter your password");
                $scope.error = "Veuillez entrer votre ancien mot de passe";
                return;
            }

            if ($scope.password.new == "" || !$scope.password.new) {
                console.log("enter a new password");
                $scope.error = "Veuillez entrer votre nouveau mot de passe"
                return;
            }

            $scope.error = "";

            sendChangePassword();

        }


        var sendChangePassword = function () {
            var url = account.url + "/nodejs/" + config.API_LEVEL + "/users/changePassword";
            console.log("sendChangePassword");
            $http({
                method: 'POST',
                url: url,
                data: {token: account.token, currentPassword: $scope.password.old, newPassword: $scope.password.new}
            })
                .error(function (data, status, headers, config) {
                    if (status === 400) {
                        let $errorMsg = data.message
                        $scope.error = $errorMsg;

                        if (data.minEntropyValue) {
                            $scope.error += `<br> La force du mot de passe doit être de <b>${data.minEntropyValue}</b>, votre mot de passe est de force <b>${data.currentEntropyValue}</b> 
<br><b>Conseil</b> : Créer un mot de passe long (environ 15 caractères) avec des majuscules, des minuscules, des chiffres et des caractères spéciaux.`
                        }

                        console.log(data)
                        $rootScope.$broadcast('notify', {
                            class: 'danger',
                            content: "<b>" + account.name + "</b> " + $errorMsg
                        });
                    }

                    if (status == 403) {
                        $scope.error = Messages.PASSWORD_OLD_ERROR;
                        $rootScope.$broadcast('notify', {
                            class: 'danger',
                            content: "<b>" + account.name + "</b> " + "Erreur dans la saisie de votre ancien mot de passe"
                        });
                    }
                    if (status == 500) {
                        $scope.error = Messages.SOMETHING_BAD_HAPPENNED;
                        $rootScope.$broadcast('notify', {
                            class: 'danger',
                            content: "<b>" + account.name + "</b> " + "Une erreur est survenue"
                        });
                    }

                })
                .success(function (data, status, headers, config) {
                    console.log("success");
                    console.log(account);
                    $rootScope.$broadcast('notify', {
                        class: 'success',
                        content: "<b>" + account.name + "</b> " + Messages.CHANGE_PASSWORD_SUCCESS
                    });
                    $modalInstance.dismiss('cancel');
                    socketioSrv.killSockets();
                    socketioSrv.initSockets();

                })
        }


    });


})();
