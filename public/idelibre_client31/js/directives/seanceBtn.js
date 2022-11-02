(function () {
    'use strict';

    angular.module('idelibreApp').directive('seanceBtn', function ($location, $rootScope, accountSrv) {


        /**
         * @namespace seanceBtn
         */
        return {
            templateUrl: 'js/directives/seanceBtn.html',
            restrict: 'E',
            replace: false,
            scope: {
                data: '=',
                account: '=',
                action: '&'
            },
            /**
             * 
             * @param {type} $scope
             * @function seanceBtn.controller
             */
            controller: function ($scope) {

                $scope.accountId = $scope.account.id;


                //ecouteur pour la mise à jour du nombre de convocation chargée
                $scope.$on('update loaded convocation', function (event, data) {

                    //si le numero de la seance correspond
                    if (data.seanceId === $scope.data.id) {
                        // on valide que la convocation est bien chargée
            //            $scope.loadedConvocationDocument = $scope.data.convocation.document_text.isLoaded;
                        // on applique la modification
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                //écouteur pour la mise à jour du nombre de morceau de convocation chargé
                $scope.$on('update loaded convocation pdfpart', function (event, data) {

                    //si le numero de la seance correspond
                    if (data.seanceId === $scope.data.id) {
                        // on valide que la convocation est bien chargée
                        $scope.countPartLoaded = $scope.data.countConvocationPdfdatasLoaded();
                        // on applique la modification
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });



                //ecouteur pour la mise à jour du nombre de projet chargée
                $scope.$on('update loaded projet', function (event, data) {
                    if (data.seanceId === $scope.data.id) {
                        $scope.loadedProjetsDocument = $scope.data.countLoadedProjets();
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });


                //ecouteur pour la mise à jour du nombre de projet chargée
                $scope.$on('update loaded other document', function (event, data) {
                    if (data.seanceId === $scope.data.id) {
                        $scope.loadedOtherdocsDocument = $scope.data.countLoadedOtherdocs();
                        if (!$rootScope.$$phase) {
                            $scope.$apply();
                        }
                    }
                });

                //écouteur qui force le rafraichissment
                $scope.$on('refresh seances', function (event, data) {
                    // rafraichissement des données des seances
                    $scope.loadedConvocationDocument = $scope.data.isLoadedConvocationDocument();
                    $scope.loadedProjetsDocument = $scope.data.countLoadedProjets();
                    $scope.loadedOtherdocsDocument = $scope.data.countLoadedOtherdocs();
                    $scope.isRead = $scope.data.convocation.isRead;
                    $scope.isModified = $scope.data.isModified;
                    $scope.countProjets = $scope.data.countProjets();
                    $scope.countOtherdocs = $scope.data.countOtherdocs();
                });


                $scope.$on('refresh_modify', function(event, data){
                    if(data.seanceId == $scope.data.id)
                        $scope.isModified = true;
                });

                $scope.$on("refreshAnnotations", function (event, data) {
                    if (!$rootScope.$$phase) {
                        $scope.$apply();
                    }
                });



                /**
                 * action au click
                 * envoie vers la convocation ou l'accusé de reception
                 * @returns {undefined}
                 */
                //now goToOdj in fact
                $scope.goToODJ = function () {
                    //si la convocation est chargé
                    if ($scope.data.isLoadedConvocationDocument() === LOADED) {

                        //on set la modification comme vu
                        if ($scope.data.isModified) {
                            $scope.data.isModified = false;
                            accountSrv.save();
                        }
                        //si la covocation à déja était lue
                        if ($scope.data.isUnreadConvocation()) {
                            //$location.path('/convocation/' + $scope.data.convocation.document_text.id + '/' + $scope.data.id + '/' + $scope.accountid);
                            $location.path('/odj/' + $scope.data.id + '/' + $scope.accountId);

                        } else {
                            // on broadcast la demande de confirmation d'accusé reception
                            // on envoie la seance encours et l'account correspondant
                            var toAdd = {seance: $scope.data, accountId: $scope.accountId}
                            //saveit in local
                            $rootScope.$broadcast('ar', toAdd);

                        }

                    }

                };



                $scope.disabled = function () {
                    if ($scope.data.isLoadedConvocationDocument() === LOADED) {
                        return ''
                    } else {
                        return 'disabled';
                    }
                }


                $scope.isUnreadAnnotation = function () {
                    var res = $scope.data.isUnreadAnnotation();
                    return res;
                };

                //nombre de projet
                $scope.countProjets = $scope.data.countProjets();
                $scope.countOtherdocs = $scope.data.countOtherdocs();



                console.log("date");
                console.log($scope.data.date);



                var date = new Date(parseInt($scope.data.date));
console.log(date.getHours());


                $scope.formatedDate = formatedDate(date);
                $scope.heure = formatedHour(date);


                if($scope.account.type == ACTEURS ) {
                    $scope.loadedConvocationDocument = $scope.data.isLoadedConvocationDocument();
                    // nombre de projet chargé
                    $scope.loadedProjetsDocument = $scope.data.countLoadedProjets();
                    $scope.loadedOtherdocsDocument = $scope.data.countLoadedOtherdocs();
                    // convocation déja lu
                    $scope.isRead = $scope.data.convocation.isRead;
                }else{
                    $scope.loadedConvocationDocument = $scope.data.isLoadedInvitation();

                    $scope.isRead = $scope.data.invitation.isRead;
                    $scope.loadedProjetsDocument = $scope.data.countLoadedProjets();
                    $scope.loadedOtherdocsDocument = $scope.data.countLoadedOtherdocs();

                }

                // seance modifiée ?
                $scope.isModified = $scope.data.isModified;



                function formatedDate(date) {
                    var month = parseInt(date.getMonth()) + 1;
                    if (month < 10) {
                        month = "0" + month;
                    }

                    var dayDate = date.getDate();
                    if (dayDate < 10) {
                        dayDate = "0" + dayDate;
                    }
                    var fdate = "" + dayDate + "/" + month + "/" + date.getFullYear();
                    return fdate;
                }

                function formatedHour(date) {

                    console.log(date);
                    console.log(date.getTimezoneOffset());

                    var hours = date.getHours();
                    if (hours < 10) {
                        hours = "0" + "" + hours;
                    }
                    var minutes = date.getMinutes()
                    if (minutes < 10) {
                        minutes = "0" + minutes;
                    }

                    var fhour = "" + hours + "h" + minutes;
                    return fhour;
                }


            }

        };

    });

})();







