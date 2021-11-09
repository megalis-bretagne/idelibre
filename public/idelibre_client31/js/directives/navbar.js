(function () {

    'use strict';

    angular.module('idelibreApp').directive('navbar', function (loginSrv, $rootScope, $timeout) {
        return {
            templateUrl: 'js/directives/navbar.html',
            restrict: 'E',
            replace: false,
            /**
             * 
             * @param {type} $scope
             * @function projetBtn.controller
             */
            controller: function ($scope) {


                $scope.username = loginSrv.username;


                $scope.menu = [
                    {titre: 'identification', lien: '#\identification', icon: 'fa fa-key'},
                    {titre: 'Accueil', lien: '#\accueil', icon: 'fa fa-home'},
                    {titre: 'Mot de passe oublié', lien: '#/forgetPassword', icon: 'fa fa-question'}
                ];


                $scope.brand = function () {

                    $scope.$broadcast('close searchbar');
                };


                $scope.isCollapsed = true;


                $scope.toggleRightDrawer = function(){
                    $rootScope.$broadcast('toggleRightDrawer', {});
                };
                
                
                $scope.toggleLeftDrawer = function(){
                    $rootScope.$broadcast('toggleLeftDrawer', {});
                };
             

                $scope.about = function () {
                    $rootScope.$broadcast('modalOpen', {title: 'À propos', content: 'idelibre: v4.1.0', about: true});
                };


                $scope.rgpd = function() {

                }
                
                
                $scope.visible = false;
                $scope.$on('buttonDrawersVisibility', function(event, data){
                    $scope.visible = data.visibility;
                });

            }


        };

    });


})();
