(function () {
    'use strict';


    angular.module('idelibreApp').controller('GlobalCtrl', function ($scope, $log, $rootScope, $location, socketioSrv, localDbSrv, $modal, ngToast, accountSrv, annotationSrv, arSrv) {


        //desactive le worker pdfjs si cordova
        if (config.cordova) {
            PDFJS.disableWorker = true;
        }

        $scope.name = 'accueil';
        $scope.$on('name', function (event, data) {
            $scope.name = data.name;

        });

        $scope.certif = {}
        $scope.$on('certificat checker', function (event, data) {
            if (data.value == 'nok') {
                $scope.certif.status = 'nok';
                $scope.certif.name = 'HTTPS';
                $scope.certif.class = 'fa fa-unlock red_color';
            } else {
                $scope.certif.status = 'ok';
                $scope.certif.name = data.value;
                $scope.certif.class = 'fa fa-lock greenFash_color';
            }
        });


        // satus de la connexion
        $scope.state = socketioSrv.status;


        localDbSrv.init();


        //Load saved Accounts !!
        accountSrv.load();
        accountSrv.getList().forEach(function (account) {
            account.status = OFFLINE;
        });
        
        
        
        annotationSrv.load();
        arSrv.load();
       
        
        
        
        
                

        $scope.accounts = accountSrv.getList();
        localDbSrv.getAllConvocation();
        localDbSrv.checkAllProjetsDocument();
        localDbSrv.checkAllOtherdocsDocument();
        socketioSrv.initSockets();





        $scope.logging = function () {
            socketioSrv.syncAccounts();
        };



        /**
         * envoie à la page d'authentification
         * @returns {}
         */
        $scope.identification = function () {
            $location.path('/identification');
        };


        /**
         * envoie à la page d'accueil
         * @returns {undefined}
         */
        $scope.backToAccueil = function () {
            $location.path('/accueil');
        };






        var onDeviceReady = function () {

            // pour les terminaux mobiles creation du repertoire idelibre
            if (cordova) {

                var fail = function (error) {
                    $log.error(error);
                };

                var success = function (res) {
                    $log.debug('folder created');

                };

                var createFolder = function (fileSystem) {
                    fileSystem.root.getDirectory('idelibre', {create: true, exclusive: false}, success, fail);
                };

                window.requestFileSystem(LocalFileSystem.PERSISTENT, 0, createFolder, fail);



            }


        };

        document.addEventListener('deviceready', onDeviceReady, false);




        //notification
        // ex : $rootScope.$broadcast('notify', {class: 'info', content: '3 seances non lues'});
        $scope.$on('notify', function (event, data) {

            ngToast.create({
                className: data.class,
                content: data.content,
                dismissOnTimeout: true,
                timeout: 4000
            });
            if (!$rootScope.$$phase) {
                $rootScope.$apply();
            }

        });




////////////////////////////////////////////////////////////////////////////
        //MODAL Information

        // pour le modal de fin de dl
        $scope.open = function (data) {

            $modal.open({
                templateUrl: 'js/templates/modalInfo/modalInfo.html',
                controller: 'ModalInfoCtrl',
                size: 'sm',
                resolve: {
                    content: function () {
                        return data.content;
                    },
                    title: function () {
                        return data.title;
                    },
                    about: function () {
                        return data.about;
                    }
                }
            });
        };


        //écouteur pour déclancher le modal
        $scope.$on('modalOpen', function (event, data) {
            $scope.open(data);
        });


    });

})();











