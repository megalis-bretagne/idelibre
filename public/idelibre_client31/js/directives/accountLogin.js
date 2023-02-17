(function () {
    'use strict';

    angular.module('idelibreApp').directive('accountLogin', function ($location, $rootScope, accountSrv, socketioSrv, $modal) {
        /**
         * @namespace accountBtn
         */
        return {
            templateUrl: 'js/directives/accountLogin.html',
            restrict: 'E',
            replace: false,
            scope: {
                account: '=',
            },
            /**
             * 
             * @param {type} $scope
             * @function accountBtn.controller
             */
            controller: function ($scope) {
                $scope.accountName = $scope.account.name;
                $scope.username = $scope.account.username;
                $scope.password = $scope.account.password;
                $scope.suffix = $scope.account.suffix;
                $scope.url = $scope.account.url;
                $scope.cordova = config.cordova;


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


                if($scope.account.status == ONLINE){
                    $scope.online  = true;
                }else{
                    $scope.online  = false;
                }

                //TODO add listener online /offline

                $scope.changePassword =function(){
                    $modal.open({
                        templateUrl: 'js/templates/modalInfo/ModalPasswordCtrl.html',
                        controller: 'ModalPasswordCtrl',
                        size: 'lg',
                        resolve: {
                            account: function () {
                                return $scope.account;
                            }
                        }
                    });

                }



                $scope.remove = function () {
                    accountSrv.delete($scope.account.id);
		    accountSrv.save();	
                }


                $scope.validate = function () {
                        //si l'url est renseingé par l'utilisateur (cas cordova)
                        if (!config.cordova) {
                           $scope.account.url = "";
                          
                        }

                        $scope.account.seances = [];



                        if(!$scope.account.token){
                         if(!$scope.account.password || !$scope.account.password === '') {
                             return $rootScope.$broadcast('notify', {class: 'danger', content: "<b>" + $scope.account.name + "</b> " + Messages.LOGIN_MISSING_PASSWORD});
                         }
                        }

                        $rootScope.$broadcast('notify', {class: 'success', content: Messages.LOGIN_CHECK});
                        socketioSrv.killSockets();

                        socketioSrv.initSockets();

                }

            }

        };

    });

})();
