(function () {
    'use strict';

    angular.module('idelibreApp').factory('dlOriginalSrv', function ($http, $log, $rootScope) {

        var dl = {};

        /**
         * telechargement d'un document originale sur le serveur en fonction du type d'utilisation (app / navigateur)
         * @param {String} url
         * @param {String} filename
         * @param {String} mimeType
         * @param {String} folder
         * @param {function} callback
         * @returns {}
         */
        dl.dlPDF = function (account, url, filename, mimeType, folder, callback, callbackError) {
            if (config.cordova) {
                dlPDFCordova(account, url, filename, mimeType, folder, callback, callbackError);
            } else {

                dlPDFnavigateur(account, url, filename, mimeType, callback, callbackError);
            }
        };



        /**
         * cas de Téléchargemtn depuis un navigateur
         * @param {type} url
         * @param {type} filename
         * @param {type} mimeType
         * @param {type} callback
         * @returns {undefined}
         */

        var dlPDFnavigateur = function (account, url, filename, mimeType, callback, callbackError) {
            var url = url;
            $rootScope.$broadcast('upload');
            //$http.get(url, {timeout: TIMEOUT * 4})

            $http({method: 'GET', url: url, responseType: "blob"
                , headers: {
                    'token': account.token}
            })
                    .success(function (data, status, headers, config) {
                        $rootScope.$broadcast('download');
                
                        var blob = data;
                        
                        if (window.navigator.msSaveOrOpenBlob) {
                            window.navigator.msSaveOrOpenBlob(blob, filename);
                        } else {
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.setAttribute('href', url);
                            a.setAttribute('style', "display: none");
                            a.setAttribute('download', filename);
                            document.body.appendChild(a);
                            a.click();
                        }
                        callback();

                    })
                    .error(function (data, status, headers, config) {
                        callbackError();
                    });
        };





        /**
         * cas du téléchargement depuis l'appli
         * @param {type} url
         * @param {type} filename
         * @param {type} mimeType
         * @param {type} folder
         * @param {type} callback
         * @returns {undefined}
         */
        var dlPDFCordova = function (account, url, filename, mimeType, folder, callback, callbackError) {

            var url = url;
            $rootScope.$broadcast('upload');
            $http({method: 'GET', url: url, responseType: "blob"
                , headers: {
                    'token': account.token}
            })
                    .success(function (data, status, headers, config) {
                        $rootScope.$broadcast('download');
                        //transformation du buffer recu en arrayBuffer
                        var blob = data;
                        storeDocument(blob, filename, folder, callback, callbackError);

                    })
                    .error(function (data, status, headers, config) {
                        callbackError();
                    });
        };




        /**
         * enregistre le pdf originale dans la memoire d'un terminal mobile
         * @param {type} blob
         * @param {type} filename
         * @param {type} folder
         * @param {type} callback
         * @returns {undefined}
         */
        var storeDocument = function (blob, filename, folder, callback, callbackError) {

            function gotFS(fileSystem) {
                fileSystem.root.getDirectory(folder, {create: true, exclusive: false}, gotDir, fail);
            }

            function gotDir(dirEntry) {
                dirEntry.getFile(filename, {create: true, exclusive: false}, gotFileEntry);
            }


            function gotFileEntry(fileEntry) {
                fileEntry.createWriter(function (fileWriter) {
                    fileWriter.onwriteend = function (e) {
                        $log.info('Write completed.');
                        callback();
                    };
                    fileWriter.onerror = function (e) {
                        $log.error('Write failed: ' + e.toString());
                    };
                    fileWriter.write(blob);
                });
            }

            function fail(error) {
                $log.error(error.code);
                callbackError();
            }

            window.requestFileSystem(LocalFileSystem.PERSISTENT, 0, gotFS, fail);

        };

        return dl;

    });

})();



