(function () {
    'use strict';

    angular.module('idelibreApp').controller('OdjCtrl', function ($scope, $log, $routeParams, $rootScope, $location, dlOriginalSrv, localDbSrv, fakeUrlSrv, accountSrv, $modal) {
        $rootScope.$broadcast('buttonDrawersVisibility', {visibility: false});

        $scope.seanceId = $routeParams.seanceId;
        $scope.accountId = $routeParams.accountId;


        var account = accountSrv.findAccountById($scope.accountId);


        var seance = account.findSeance($scope.seanceId);
        console.log(seance);

        $scope.isInvite = account.type == INVITES;

        if (seance.convocation) {
            $scope.annotations = seance.convocation.countAnnotations();
        }

        //récupération de la liste des id de toutes les annexes pdf de la séance en cours
        var annexesPdf = [];
        _.each(seance.projets, function (projet) {
            _.each(projet.annexes, function (annexe) {
                if (annexe.type === "application/pdf") {
                    annexesPdf.push(annexe.id);
                }
            });
        });

        //demande de verification de chargement des annexes
        //   localDbSrv.checkAnnexesPDF(annexesPdf);


        //tri des projet par ...
        $scope.sortProjet = 'rank';
        $scope.sortAnnexe = 'annexe_rank';
        $scope.sortOtherdoc = 'rank';


        $scope.$on("sortSeanceBy", function (event, data) {
            $scope.sortBy(data.sortBy)
        });

        $scope.sortBy = function (arg) {
            if ($scope.sortProjet == arg) {
                $scope.sortProjet = '-' + $scope.sortProjet;
            } else {
                $scope.sortProjet = arg;
            }
        };

        $scope.sortBy = function (arg) {
            if ($scope.sortOtherdoc == arg) {
                $scope.sortOtherdoc = '-' + $scope.sortOtherdoc;
            } else {
                $scope.sortOtherdoc = arg;
            }
        };

        $scope.sortBy = function (arg) {
            if ($scope.sortAnnexe == arg) {
                $scope.sortAnnexe = '-' + $scope.sortAnnexe;
            } else {
                $scope.sortAnnexe = arg;
            }
        };
        $scope.collectivite = account.name;
        $scope.seance = seance;


        var date = new Date(parseInt(seance.date));
        var formatedDate = formatedDate(date);
        var hour = formatedHour(date);


        // on envoie le nom du document
        $rootScope.$broadcast('name', {
            name: formatedDate + " à " + hour
        });


        /**
         * retour à la liste des seances
         * @returns {}
         */
        $scope.goToseance = function () {
            $location.path('/seance/' + $scope.accountId);
        };


        $scope.clickOnConvocation = function () {
            //TODO : a way to reload convocation !
            //always loaded else you can't go there !!!
            // !

            if (seance.convocation) {
                $location.path('/convocation/' + seance.convocation.document_text.id + '/' + seance.id + '/' + $scope.accountId);
            } else if (seance.invitation) {
                $location.path('/convocation/' + seance.invitation.document_text.id + '/' + seance.id + '/' + $scope.accountId);
            }
        };


        $scope.downloadAll = function () {
            //sort projet by rank :
            var sortedProjet = _.sortBy(seance.projets, function (projet) {
                return projet.rank;
            });
            _.each(sortedProjet, function (projet) {
                if (projet.document_text.isLoaded !== 2) {
                    localDbSrv.getProjet(projet.document_text, $scope.collectivite, $scope.seance, $scope.accountId);
                }
            });

            var sortedOtherdoc = _.sortBy(seance.otherdocs, function (otherdoc) {
                return otherdoc.rank;
            });

            _.each(sortedOtherdoc, function (otherdoc) {
                if (otherdoc.document_text.isLoaded !== 2) {
                    localDbSrv.getProjet(otherdoc.document_text, $scope.collectivite, $scope.seance, $scope.accountId);
                }
            });

            _.each(sortedProjet, function (projet) {
                _.each(projet.annexes, function (annexe) {
                    if (annexe.annexe_name.slice(-3) == "pdf" && annexe.loaded == 0) {
                        localDbSrv.addAnnexeToDownload(account.name, seance, account.id, annexe);
                    }
                });
            });
        };


        $scope.popupPresence = function () {
            $log.debug("popupPresence");
            $modal.open({
                templateUrl: 'js/templates/modalInfo/ModalpresenceCtrl.html',
                controller: 'ModalpresenceCtrl',
                size: 'md',
                resolve: {
                    account: function () {
                        return account;
                    },
                    seance: function () {
                        return seance;
                    }
                },

            })
                .result.then(function (res) {
                if (res === Seance.ABSENT) {
                    openConfirmAbsent()
                }
            });
        };


        var openConfirmAbsent = function () {
            $modal.open({
                templateUrl: 'js/templates/modalInfo/ModalConfirmAbsent.html',
                controller: 'ModalConfirmAbsentCtrl',
                size: 'md',
                resolve: {
                    account: function () {
                        return account;
                    },
                    seance: function () {
                        return seance;
                    }
                },

            })
        }

///// pour recia (avec api level 0.1.0)
        var callbackSuccess = function () {
            $scope.dlStatus = false;
            $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";
            $rootScope.$broadcast('modalOpen', {
                title: 'Téléchargement terminé',
                content: 'Votre document a bien été téléchargé'
            });
        }


        var callbackError = function () {
            $scope.dlStatus = false;
            $scope.stateClass = "fa fa-download fa-lg perso-color-yellow";
            $rootScope.$broadcast('modalOpen', {
                title: 'Téléchargement erreur',
                content: 'Votre document n\'a pas été téléchargé'
            });
        };

        $scope.downloadZipSeance = function () {
            //function (account, url, filename, mimeType, callback, callbackError)
            var url = account.url + '/nodejs/' + config.API_LEVEL + '/zips/dlZip/' + $scope.seanceId;
            var filename = seance.name + '_' + DateUtils.formattedDateZip(seance.date) + '.zip';
            $rootScope.$broadcast('notify', {
                class: 'info',
                content: "<b>" + account.name + "</b>" + Messages.DONWLOAD_ZIP
            });
            dlOriginalSrv.dlPDF(account, url, filename, 'application/zip', "idelibre/", callbackSuccess, callbackError);
        };


        $scope.downloadPdfSeance = function () {
            var url = account.url + '/nodejs/' + config.API_LEVEL + '/pdf/dlPdf/' + $scope.seanceId;
            var filename = 'seance.pdf';
            $rootScope.$broadcast('notify', {
                class: 'info',
                content: "<b>" + account.name + "</b>" + Messages.DONWLOAD_PDF
            });
            dlOriginalSrv.dlPDF(account, url, filename, 'application/pdf', "idelibre/", callbackSuccess, callbackError);
        };


        //TODO UTILITY CLASS !!
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


/////////


        $scope.popupSort = function () {
            $log.debug("popupSort");

            $modal.open({
                templateUrl: 'js/templates/modalInfo/ModalSortProjetCtrl.html',
                controller: 'ModalSortProjetCtrl',
                size: 'sm',
                resolve: {
                    content: function () {
                        "return data.content";
                    },
                    title: function () {
                        "return data.title";
                    },
                    about: function () {
                        "return data.about";
                    }
                }
            });

        };


    });
})();
