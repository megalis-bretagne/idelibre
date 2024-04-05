(function () {
    'use strict';

    angular.module('idelibreApp').factory('attendanceSrv', function ($http, $log, $rootScope) {

        let attendance = {};

        attendance.getAttendanceStatus = function (account, sittingId, callback, callbackError) {
            let url = account.url + "/nodejs/" + config.API_LEVEL + '/attendance/sittings/' + sittingId;
            $http({
                method: 'GET', url: url, responseType: "json"
                , headers: {
                    'token': account.token
                }
            }).success(data => {
                callback(data);
            }).error(err => {
                    console.log(err);
                    callbackError();
                }
            )
        }

        attendance.postAttendanceStatus = function (account, sittingId, attendanceStatus, callback, callbackError) {
            let url = account.url + "/nodejs/" + config.API_LEVEL + '/attendance/sittings/' + sittingId;
            let toSend = {
                attendanceStatus: {attendance: attendanceStatus.attendance, mandatorId: attendanceStatus.mandatorId},
                token: account.token
            }

            $http({
                method: 'POST',
                url: url,
                data: toSend,
                headers: {
                    'Content-Type': 'application/json'
                }
            }).then(function (reponse) {
                callback();

            }, function (erreur) {
                callbackError();
            });

        }


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

            $http({
                method: 'GET', url: url, responseType: "blob"
                , headers: {
                    'token': account.token
                }
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

        return attendance;

    });

})();



